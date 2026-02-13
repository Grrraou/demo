<?php

namespace App\Http\Controllers\Web\Blog;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $ownedCompanyId = (int) $request->session()->get('current_owned_company_id');
        if (! $ownedCompanyId) {
            abort(403, 'No company selected.');
        }

        $articles = Article::query()
            ->where('owned_company_id', $ownedCompanyId)
            ->with('author:id,name')
            ->orderByDesc('updated_at')
            ->paginate($request->integer('per_page', 15));

        return view('blog.articles.index', compact('articles'));
    }

    public function create(Request $request): View
    {
        $ownedCompanyId = (int) $request->session()->get('current_owned_company_id');
        if (! $ownedCompanyId) {
            abort(403, 'No company selected.');
        }
        if (! $request->user()->canCreateArticles()) {
            abort(403, 'Forbidden');
        }

        $article = new Article([
            'draft' => true,
            'owned_company_id' => $ownedCompanyId,
            'author_id' => $request->user()->id,
        ]);

        return view('blog.articles.edit', [
            'article' => $article,
            'isCreate' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $ownedCompanyId = (int) $request->session()->get('current_owned_company_id');
        if (! $ownedCompanyId) {
            abort(403, 'No company selected.');
        }
        if (! $request->user()->canCreateArticles()) {
            abort(403, 'Forbidden');
        }

        $validated = $this->validateArticle($request, $ownedCompanyId);
        unset($validated['image']);
        $validated['owned_company_id'] = $ownedCompanyId;
        $validated['author_id'] = $request->user()->id;
        $validated['draft'] = true;
        $validated['public'] = $request->boolean('public');
        $validated['published_at'] = null;

        $article = Article::create($validated);
        $this->handleImageUpload($request, $article);

        return redirect()->route('blog.articles.edit', $article)->with('success', 'Article created.');
    }

    public function edit(Request $request, Article $article): View|RedirectResponse
    {
        $ownedCompanyId = (int) $request->session()->get('current_owned_company_id');
        if ($article->owned_company_id !== $ownedCompanyId) {
            abort(404);
        }
        if (! $request->user()->canEditArticles()) {
            abort(403, 'Forbidden');
        }

        $article->refresh();
        $article->load('author:id,name', 'ownedCompany:id,slug,name');

        return view('blog.articles.edit', [
            'article' => $article,
            'isCreate' => false,
        ]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $ownedCompanyId = (int) $request->session()->get('current_owned_company_id');
        if ($article->owned_company_id !== $ownedCompanyId) {
            abort(404);
        }
        if (! $request->user()->canEditArticles()) {
            abort(403, 'Forbidden');
        }

        $validated = $this->validateArticle($request, $ownedCompanyId, $article->id);
        unset($validated['image']);
        $article->update($validated);
        $this->handleImageUpload($request, $article);

        return redirect()->route('blog.articles.edit', $article)->with('success', 'Article updated.');
    }

    public function publish(Request $request, Article $article): RedirectResponse
    {
        $ownedCompanyId = (int) $request->session()->get('current_owned_company_id');
        if ($article->owned_company_id !== $ownedCompanyId) {
            abort(404);
        }
        if (! $request->user()->canEditArticles()) {
            abort(403, 'Forbidden');
        }

        $article->update([
            'draft' => false,
            'published_at' => $article->published_at ?? now(),
        ]);
        $article->refresh();

        return redirect()->route('blog.articles.edit', $article)->with('success', 'Article published.');
    }

    public function unpublish(Request $request, Article $article): RedirectResponse
    {
        $ownedCompanyId = (int) $request->session()->get('current_owned_company_id');
        if ($article->owned_company_id !== $ownedCompanyId) {
            abort(404);
        }
        if (! $request->user()->canEditArticles()) {
            abort(403, 'Forbidden');
        }

        $article->update([
            'draft' => true,
            'published_at' => null,
        ]);
        $article->refresh();

        return redirect()->route('blog.articles.edit', $article)->with('success', 'Article unpublished.');
    }

    public function destroy(Request $request, Article $article): RedirectResponse
    {
        $ownedCompanyId = (int) $request->session()->get('current_owned_company_id');
        if ($article->owned_company_id !== $ownedCompanyId) {
            abort(404);
        }
        if (! $request->user()->canEditArticles()) {
            abort(403, 'Forbidden');
        }

        if ($article->image) {
            $path = public_path(Article::IMAGE_DIR . '/' . basename($article->image));
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        $article->delete();

        return redirect()->route('blog.articles.index')->with('success', 'Article deleted.');
    }

    /** Public index: /blog/{companySlug} — list published public articles for that company. */
    public function indexPublic(Request $request, string $companySlug): View
    {
        $company = \App\Models\OwnedCompany::where('slug', $companySlug)->firstOrFail();

        $articles = Article::query()
            ->where('owned_company_id', $company->id)
            ->where('draft', false)
            ->whereNotNull('published_at')
            ->where('public', true)
            ->with('author:id,name')
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('blog.index', compact('company', 'articles'));
    }

    /** Public view: /blog/{companySlug}/{articleSlug} — published articles; non-public require auth. */
    public function showPublic(Request $request, string $companySlug, string $articleSlug): View|RedirectResponse
    {
        $company = \App\Models\OwnedCompany::where('slug', $companySlug)->firstOrFail();
        $article = Article::query()
            ->where('owned_company_id', $company->id)
            ->where('slug', $articleSlug)
            ->with('author:id,name', 'ownedCompany:id,slug,name')
            ->firstOrFail();

        if (! $article->isPublished()) {
            abort(404);
        }

        if (! $article->public && ! $request->user()) {
            return redirect()->guest('/');
        }

        return view('blog.articles.show', compact('article'));
    }

    public function keywords(Request $request)
    {
        $ownedCompanyId = (int) $request->session()->get('current_owned_company_id');
        if (! $ownedCompanyId) {
            return response()->json([]);
        }

        $keywords = Article::query()
            ->where('owned_company_id', $ownedCompanyId)
            ->whereNotNull('keywords')
            ->get('keywords')
            ->pluck('keywords')
            ->flatten()
            ->unique()
            ->filter()
            ->values();

        $q = $request->string('q')->trim();
        if ($q->isNotEmpty()) {
            $keywords = $keywords->filter(fn ($k) => stripos($k, $q->toString()) !== false)->values();
        }

        return response()->json($keywords->take(20)->values()->all());
    }

    private function validateArticle(Request $request, int $ownedCompanyId, ?int $ignoreArticleId = null): array
    {
        $slugRule = Rule::unique('articles', 'slug')
            ->where('owned_company_id', $ownedCompanyId);
        if ($ignoreArticleId) {
            $slugRule->ignore($ignoreArticleId);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slugRule],
            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string', 'max:100'],
            'content' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'public' => ['boolean'],
        ]);

        $validated['keywords'] = array_values(array_filter($validated['keywords'] ?? []));
        $validated['public'] = $request->boolean('public');

        return $validated;
    }

    private function handleImageUpload(Request $request, Article $article): void
    {
        if (! $request->hasFile('image')) {
            return;
        }

        $dir = public_path(Article::IMAGE_DIR);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = $request->file('image');
        $name = $article->id . '-' . Str::random(12) . '.' . $file->getClientOriginalExtension();
        $path = $dir . '/' . $name;

        if ($article->image && file_exists(public_path(Article::IMAGE_DIR . '/' . basename($article->image)))) {
            @unlink(public_path(Article::IMAGE_DIR . '/' . basename($article->image)));
        }

        $file->move($dir, $name);
        $article->update(['image' => $name]);
    }
}
