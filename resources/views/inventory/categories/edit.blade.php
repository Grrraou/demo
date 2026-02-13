@extends('layouts.app')

@section('title', $isCreate ? 'New category' : 'Edit category: ' . $category->name)

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('inventory.categories.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Categories</a>
        </div>

        @if (session('success'))
            <p class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</p>
        @endif
        @if ($errors->any())
            <ul class="mb-4 p-3 bg-red-100 text-red-800 rounded list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <h1 class="text-xl font-bold text-gray-900 mb-6">{{ $isCreate ? 'New category' : 'Edit category' }}</h1>

            <form action="{{ $isCreate ? route('inventory.categories.store') : route('inventory.categories.update', $category) }}" method="POST" class="space-y-4">
                @csrf
                @if (! $isCreate) @method('PUT') @endif
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required
                           class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="parent_id" class="block text-sm font-medium text-gray-700">Parent category</label>
                    <select name="parent_id" id="parent_id"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— None —</option>
                        @foreach ($categories->whereNotIn('id', $isCreate ? [] : [$category->id]) as $cat)
                            <option value="{{ $cat->id }}" {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Save</button>
                    <a href="{{ route('inventory.categories.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
