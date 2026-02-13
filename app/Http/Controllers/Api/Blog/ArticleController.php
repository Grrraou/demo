<?php

namespace App\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $ownedCompanyId = (int) $request->header('X-Owned-Company-Id');
        if (! $ownedCompanyId && $user) {
            $ownedCompanyId = $user->ownedCompanies()->value('id') ?? 0;
        }
        if (! $ownedCompanyId) {
            return response()->json(['message' => 'No company selected. Set X-Owned-Company-Id header or ensure user has a company.'], 403);
        }

        $articles = Article::query()
            ->where('owned_company_id', $ownedCompanyId)
            ->with('author:id,name')
            ->orderByDesc('updated_at')
            ->paginate($request->integer('per_page', 15));

        return response()->json($articles);
    }

    public function show(Request $request, Article $article): JsonResponse
    {
        $user = $request->user();
        $ownedCompanyId = (int) $request->header('X-Owned-Company-Id');
        if (! $ownedCompanyId && $user) {
            $ownedCompanyId = $user->ownedCompanies()->value('id') ?? 0;
        }
        if (! $ownedCompanyId || $article->owned_company_id !== $ownedCompanyId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $article->load('author:id,name', 'ownedCompany:id,slug,name');

        return response()->json($article);
    }
}
