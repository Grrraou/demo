<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $article->name }} - {{ $article->ownedCompany->name }}</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="antialiased bg-gray-50">
    <div class="border-b border-gray-200 bg-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <a href="{{ route('blog.index.public', $article->ownedCompany->slug) }}" class="text-sm text-gray-600 hover:text-gray-900">← All articles</a>
        </div>
    </div>
    <article class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <header class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ $article->name }}</h1>
                <p class="mt-2 text-sm text-gray-500">
                    {{ $article->ownedCompany->name }}
                    @if ($article->author)
                        · {{ $article->author->name }}
                    @endif
                    @if ($article->published_at)
                        · {{ $article->published_at->format('M j, Y') }}
                    @endif
                </p>
            </header>
            @if ($article->imageUrl())
                <figure class="mb-6">
                    <img src="{{ $article->imageUrl() }}" alt="" class="w-full rounded-lg border border-gray-200">
                </figure>
            @endif
            <div class="prose prose-indigo max-w-none">
                {!! $article->content !!}
            </div>
        </div>
    </article>
</body>
</html>
