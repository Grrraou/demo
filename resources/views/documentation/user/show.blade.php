@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <nav class="flex items-center space-x-2 text-sm text-gray-500">
            <a href="{{ route('docs.index') }}" class="hover:text-gray-700">Documentation</a>
            <span>/</span>
            <a href="{{ route('docs.user.getting-started') }}" class="hover:text-gray-700">User Guides</a>
            <span>/</span>
            <span class="text-gray-900">{{ $title }}</span>
        </nav>
    </div>

    <div class="flex gap-8">
        <!-- Sidebar -->
        <div class="flex-shrink-0">
            @include('documentation._sidebar')
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="prose prose-lg max-w-none">
                    @include('documentation.user._styles')
                    {!! $content !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
