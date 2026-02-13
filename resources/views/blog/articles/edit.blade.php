@extends('layouts.app')

@section('title', $isCreate ? 'New article' : 'Edit: ' . $article->name)

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('blog.articles.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Articles</a>
        </div>

        @if (session('success'))
            <p class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</p>
        @endif

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-6">
                <h1 class="text-xl font-bold text-gray-900">{{ $isCreate ? 'New article' : 'Edit article' }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-sm font-medium
                    @if ($isCreate)
                        bg-gray-100 text-gray-700
                    @elseif ($article->isPublished())
                        bg-green-100 text-green-800
                    @else
                        bg-amber-100 text-amber-800
                    @endif">
                    @if ($isCreate)
                        Not created
                    @elseif ($article->isPublished())
                        Published since {{ $article->published_at->format('M j, Y \a\t g:i A') }}
                    @else
                        Draft
                    @endif
                </span>
            </div>

            <form action="{{ $isCreate ? route('blog.articles.store') : route('blog.articles.update', $article) }}" method="POST" enctype="multipart/form-data" class="space-y-4" id="article-form">
                @csrf
                @if (! $isCreate)
                    @method('PUT')
                @endif
                @if ($errors->any())
                    <ul class="text-sm text-red-600 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $article->name) }}" required
                           class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700">Slug (URL)</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $article->slug) }}" required
                           pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                           placeholder="my-article"
                           class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Lowercase letters, numbers and hyphens only. Unique per company.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keywords</label>
                    <div class="flex flex-wrap gap-2 items-center border border-gray-300 rounded-md px-2 py-2 min-h-[42px]" id="keywords-container">
                        <input type="text" id="keywords-input" autocomplete="off" placeholder="Type and press Enter or select..."
                               class="flex-1 min-w-[120px] border-0 p-0 shadow-none focus:ring-0 text-sm">
                    </div>
                    <datalist id="keywords-datalist"></datalist>
                    <div id="keywords-hidden-container"></div>
                    <p class="mt-1 text-xs text-gray-500">Suggestions from existing articles will appear.</p>
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                    <input type="hidden" name="content" id="content" value="">
                    <div id="content-editor" class="mt-1 rounded-md border border-gray-300 bg-white min-h-[320px]"></div>
                </div>

                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
                    @if ($article->imageUrl())
                        <div class="mt-1 mb-2">
                            <img src="{{ $article->imageUrl() }}" alt="" class="h-24 w-auto rounded border border-gray-200">
                        </div>
                    @endif
                    <input type="file" name="image" id="image" accept="image/*"
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-indigo-50 file:text-indigo-700">
                </div>

                <div>
                    <label class="inline-flex items-center gap-2">
                        <input type="hidden" name="public" value="0">
                        <input type="checkbox" name="public" value="1" {{ old('public', $article->public ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">Public (visible to everyone when published)</span>
                    </label>
                </div>

                @if (! $isCreate && $article->author)
                    <p class="text-sm text-gray-500">Author: {{ $article->author->name }}</p>
                @endif
            </form>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button type="submit" form="article-form" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    {{ $isCreate ? 'Create' : 'Save changes' }}
                </button>
                @if (! $isCreate)
                    @if ($article->isPublished())
                        <a href="{{ $article->publicUrl() }}" target="_blank" rel="noopener noreferrer" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 border border-gray-300">
                            View
                        </a>
                        <form action="{{ route('blog.articles.unpublish', $article) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-md hover:bg-amber-700">
                                Unpublish
                            </button>
                        </form>
                    @else
                        <form action="{{ route('blog.articles.publish', $article) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                                Publish
                            </button>
                        </form>
                    @endif
                @endif
                <a href="{{ route('blog.articles.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                @if (! $isCreate)
                    <form action="{{ route('blog.articles.destroy', $article) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this article? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 text-red-600 border border-red-300 rounded-md text-sm font-medium hover:bg-red-50">
                            Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('article-form');
    const keywordsContainer = document.getElementById('keywords-container');
    const keywordsInput = document.getElementById('keywords-input');
    const keywordsHiddenContainer = document.getElementById('keywords-hidden-container');
    const datalist = document.getElementById('keywords-datalist');

    const existingKeywords = @json(old('keywords', $article->keywords ?? []));
    let keywordSet = new Set(existingKeywords);

    function syncHiddenInputs() {
        keywordsHiddenContainer.innerHTML = '';
        keywordSet.forEach(function(k) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'keywords[]';
            input.value = k;
            keywordsHiddenContainer.appendChild(input);
        });
    }

    function renderTags() {
        const inputEl = keywordsInput;
        keywordsContainer.querySelectorAll('.keyword-tag').forEach(el => el.remove());
        keywordSet.forEach(function(k) {
            const span = document.createElement('span');
            span.className = 'keyword-tag inline-flex items-center gap-1 px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-sm';
            span.textContent = k;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'text-gray-400 hover:text-red-600';
            btn.innerHTML = '×';
            btn.addEventListener('click', function() {
                keywordSet.delete(k);
                renderTags();
                syncHiddenInputs();
            });
            span.appendChild(btn);
            keywordsContainer.insertBefore(span, inputEl);
        });
        syncHiddenInputs();
    }

    function fetchSuggestions(q) {
        const url = '{{ route("blog.articles.keywords") }}' + (q ? '?q=' + encodeURIComponent(q) : '');
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(function(keywords) {
                datalist.innerHTML = '';
                keywords.forEach(function(k) {
                    const opt = document.createElement('option');
                    opt.value = k;
                    datalist.appendChild(opt);
                });
            });
    }

    keywordsInput.addEventListener('input', function() {
        fetchSuggestions(this.value);
    });
    keywordsInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const v = this.value.trim();
            if (v && !keywordSet.has(v)) {
                keywordSet.add(v);
                renderTags();
                this.value = '';
            }
        }
    });
    keywordsInput.setAttribute('list', 'keywords-datalist');
    keywordsInput.addEventListener('change', function() {
        const v = this.value.trim();
        if (v && !keywordSet.has(v)) {
            keywordSet.add(v);
            renderTags();
            this.value = '';
        }
    });

    renderTags();
    fetchSuggestions('');

    const contentEl = document.getElementById('content');
    const initialContent = @json(old('content', $article->content ?? ''));
    if (contentEl && typeof Quill !== 'undefined') {
        const quill = new Quill('#content-editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['link'],
                    ['clean']
                ]
            }
        });
        quill.root.innerHTML = initialContent || '';
        form.addEventListener('submit', function() {
            contentEl.value = quill.root.innerHTML;
        });
    }
});
</script>
@endsection
