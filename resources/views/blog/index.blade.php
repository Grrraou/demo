<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Blog — {{ $company->name }}</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="antialiased bg-gray-50">
    <div class="min-h-screen">
        <header class="border-b border-gray-200 bg-white shadow-sm">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <a href="{{ url('/') }}" class="text-xl font-semibold text-gray-900">{{ config('app.name') }}</a>
                <span class="text-gray-400 mx-2">/</span>
                <span class="text-gray-700">Blog — {{ $company->name }}</span>
            </div>
        </header>

        <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-2xl font-bold text-gray-900 mb-8">Published articles</h1>

            @if ($articles->isEmpty())
                <p class="text-gray-500">No articles yet.</p>
            @else
                <ul class="space-y-6">
                    @foreach ($articles as $article)
                        <li>
                            <a href="{{ $article->publicUrl() }}" class="block group">
                                <article class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                    <div class="sm:flex">
                                        @if ($article->imageUrl())
                                            <div class="sm:shrink-0 sm:w-48 h-36 sm:h-auto">
                                                <img src="{{ $article->imageUrl() }}" alt="" class="w-full h-full object-cover">
                                            </div>
                                        @endif
                                        <div class="p-4 flex-1">
                                            <h2 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600">{{ $article->name }}</h2>
                                            <p class="mt-1 text-sm text-gray-500">
                                                @if ($article->published_at)
                                                    {{ $article->published_at->format('M j, Y') }}
                                                @endif
                                            </p>
                                            @if ($article->author)
                                                <p class="mt-0.5 text-sm text-gray-400">By {{ $article->author->name }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if ($articles->hasPages())
                    <div class="mt-8">
                        {{ $articles->links() }}
                    </div>
                @endif
            @endif
        </main>
    </div>
</body>
</html>
