@extends('layouts.app')

@section('title', 'Sales – Quotes')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-start mb-6 gap-4">
            <div>
                <a href="{{ route('home') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Home</a>
                <h1 class="text-2xl font-bold text-gray-900 mt-1">Sales – Quotes</h1>
                <nav class="flex gap-2 text-sm text-gray-500 mt-2">
                    <a href="{{ route('customers.companies.index') }}" class="hover:text-gray-700">Customers</a>
                    <span>/</span>
                    <a href="{{ route('sales.quotes.index') }}" class="text-indigo-600 font-medium">Quotes</a>
                    <span>/</span>
                    <a href="{{ route('sales.orders.index') }}" class="hover:text-gray-700">Orders</a>
                    <span>/</span>
                    <a href="{{ route('sales.deliveries.index') }}" class="hover:text-gray-700">Deliveries</a>
                    <span>/</span>
                    <a href="{{ route('sales.invoices.index') }}" class="hover:text-gray-700">Invoices</a>
                </nav>
            </div>
            <a href="{{ route('sales.quotes.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 shrink-0">New quote</a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valid until</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($quotes as $quote)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $quote->number }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $quote->customer?->name ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $quote->status?->value ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $quote->valid_until?->format('Y-m-d') ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($quote->total ?? 0, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <a href="{{ route('sales.quotes.show', $quote) }}" class="text-indigo-600 hover:text-indigo-800">View</a>
                                @if ($quote->status?->value === 'draft')
                                    <span class="mx-1">|</span>
                                    <a href="{{ route('sales.quotes.edit', $quote) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">No quotes yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($quotes->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $quotes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
