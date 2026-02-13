@extends('layouts.app')

@section('title', 'Inventory – Products')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <a href="{{ route('home') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Home</a>
                <h1 class="text-2xl font-bold text-gray-900 mt-1">Products</h1>
            </div>
            @if (auth()->user()->canEditInventory())
                <a href="{{ route('inventory.products.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">New product</a>
            @endif
            <nav class="flex gap-2 text-sm text-gray-500">
                <a href="{{ route('inventory.products.index') }}" class="text-indigo-600 font-medium">Products</a>
                <span>/</span>
                <a href="{{ route('inventory.categories.index') }}" class="hover:text-gray-700">Categories</a>
                <span>/</span>
                <a href="{{ route('inventory.units.index') }}" class="hover:text-gray-700">Units</a>
                <span>/</span>
                <a href="{{ route('inventory.suppliers.index') }}" class="hover:text-gray-700">Suppliers</a>
                <span>/</span>
                <a href="{{ route('inventory.stock-locations.index') }}" class="hover:text-gray-700">Locations</a>
                <span>/</span>
                <a href="{{ route('inventory.stocks.index') }}" class="hover:text-gray-700">Stock</a>
            </nav>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                        @if (auth()->user()->canEditInventory())
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($products as $product)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->sku }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $product->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $product->category?->name ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $product->unit?->symbol ?? '—' }}</td>
                            @if (auth()->user()->canEditInventory())
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('inventory.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->canEditInventory() ? 5 : 4 }}" class="px-6 py-8 text-center text-gray-500">No products. Run the inventory seeder or use the API to add data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($products->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
