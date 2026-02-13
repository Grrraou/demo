@extends('layouts.app')

@section('title', 'Inventory – Units')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <a href="{{ route('home') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Home</a>
                <h1 class="text-2xl font-bold text-gray-900 mt-1">Units</h1>
            </div>
            @if (auth()->user()->canEditInventory())
                <a href="{{ route('inventory.units.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">New unit</a>
            @endif
        </div>

        @if (session('success'))
            <p class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</p>
        @endif

        <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Symbol</th>
                        @if (auth()->user()->canEditInventory())
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($units as $unit)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $unit->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $unit->symbol }}</td>
                            @if (auth()->user()->canEditInventory())
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('inventory.units.edit', $unit) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->canEditInventory() ? 3 : 2 }}" class="px-6 py-8 text-center text-gray-500">No units. Run the inventory seeder or add units to use in products.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
