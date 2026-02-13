@extends('layouts.app')

@section('title', 'Articles')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Articles</h1>
            <div class="flex gap-2">
                <a href="{{ route('home') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Home</a>
                @if (auth()->user()->canCreateArticles())
                    <a href="{{ route('blog.articles.create') }}" class="px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">New article</a>
                @endif
            </div>
        </div>

        <p class="mb-4 text-sm text-gray-600">Articles for the current company.</p>

        <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Author</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Updated</th>
                        @if (auth()->user()->canEditArticles())
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($articles as $article)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $article->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $article->slug }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $article->author?->name ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if ($article->draft)
                                    <span class="text-amber-600">Draft</span>
                                @else
                                    <span class="text-green-600">Published</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $article->updated_at->format('Y-m-d H:i') }}</td>
                            @if (auth()->user()->canEditArticles())
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('blog.articles.edit', $article) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->canEditArticles() ? 6 : 5 }}" class="px-6 py-8 text-center text-gray-500">No articles yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($articles->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $articles->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
