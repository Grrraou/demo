@extends('layouts.app')

@section('title', 'Inventory – Stock')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <a href="{{ route('home') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Home</a>
                <h1 class="text-2xl font-bold text-gray-900 mt-1">Stock</h1>
            </div>
            <nav class="flex gap-2 text-sm text-gray-500">
                <a href="{{ route('inventory.products.index') }}" class="hover:text-gray-700">Products</a>
                <span>/</span>
                <a href="{{ route('inventory.categories.index') }}" class="hover:text-gray-700">Categories</a>
                <span>/</span>
                <a href="{{ route('inventory.units.index') }}" class="hover:text-gray-700">Units</a>
                <span>/</span>
                <a href="{{ route('inventory.suppliers.index') }}" class="hover:text-gray-700">Suppliers</a>
                <span>/</span>
                <a href="{{ route('inventory.stock-locations.index') }}" class="hover:text-gray-700">Locations</a>
                <span>/</span>
                <a href="{{ route('inventory.stocks.index') }}" class="text-indigo-600 font-medium">Stock</a>
            </nav>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Reserved</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Available</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($stocks as $stock)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $stock->product?->name ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $stock->location?->name ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format((float) $stock->quantity, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">{{ number_format((float) $stock->reserved, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($stock->available, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No stock records. Run the inventory seeder or use the API to add data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
