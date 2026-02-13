@extends('layouts.app')

@section('title', 'Company: ' . $ownedCompany->name)

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('admin.companies.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Manage companies</a>
        </div>

        @if (session('success'))
            <p class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</p>
        @endif

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <h1 class="text-xl font-bold text-gray-900 mb-6">Company details</h1>
            <p class="text-sm text-gray-500 mb-6">Employees with access: {{ $ownedCompany->employees_count ?? 0 }}</p>

            <form action="{{ route('admin.companies.update', $ownedCompany) }}" method="POST" class="space-y-4" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @if ($errors->any())
                    <ul class="text-sm text-red-600 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $ownedCompany->name) }}" required
                           class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $ownedCompany->slug) }}" required
                           class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <input type="text" name="description" id="description" value="{{ old('description', $ownedCompany->description) }}"
                           class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700">Header color</label>
                    <p class="text-xs text-gray-500 mt-0.5 mb-1">Used in the app header gradient to identify the current company.</p>
                    <div class="flex items-center gap-3 mt-1">
                        <input type="color" id="color-picker" value="{{ old('color', $ownedCompany->color ?? '#6366f1') }}"
                               class="h-10 w-14 rounded border border-gray-300 cursor-pointer p-1 bg-white">
                        <input type="text" name="color" id="color" value="{{ old('color', $ownedCompany->color ?? '#6366f1') }}" placeholder="#6366f1"
                               maxlength="7" pattern="#[A-Fa-f0-9]{6}"
                               class="block w-28 rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                    </div>
                    <script>
                        document.getElementById('color-picker').addEventListener('input', function(e) {
                            document.getElementById('color').value = e.target.value;
                        });
                        document.getElementById('color').addEventListener('input', function(e) {
                            var hex = e.target.value;
                            if (/^#[A-Fa-f0-9]{6}$/.test(hex)) {
                                document.getElementById('color-picker').value = hex;
                            }
                        });
                    </script>
                </div>
                <div>
                    <span class="block text-sm font-medium text-gray-700">Logo</span>
                    <p class="text-xs text-gray-500 mt-0.5 mb-1">Shown in the app header next to the company selector. Recommended: square image, max 2 MB.</p>
                    @if ($ownedCompany->logoUrl())
                        <div class="mt-2 flex items-center gap-4">
                            <img src="{{ $ownedCompany->logoUrl() }}" alt="{{ $ownedCompany->name }} logo" class="h-12 w-auto max-w-[120px] object-contain border border-gray-200 rounded">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" name="remove_logo" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                Remove logo
                            </label>
                        </div>
                    @endif
                    <input type="file" name="logo" id="logo" accept="image/*"
                           class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
