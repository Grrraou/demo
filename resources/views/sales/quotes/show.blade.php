@extends('layouts.app')

@section('title', 'Quote ' . $quote->number)

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('sales.quotes.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Quotes</a>
        </div>

        @if (session('success'))
            <p class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</p>
        @endif

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <div class="flex justify-between items-start mb-6">
                <h1 class="text-xl font-bold text-gray-900">Quote {{ $quote->number }}</h1>
                <div class="flex items-center gap-2">
                    <a href="{{ route('sales.quotes.pdf', $quote) }}" target="_blank" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Download PDF</a>
                    <span class="px-2 py-1 text-sm rounded bg-gray-100">{{ $quote->status->value }}</span>
                </div>
            </div>

            <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
                <div>
                    <dt class="text-sm text-gray-500">Customer</dt>
                    <dd class="font-medium text-gray-900">{{ $quote->customer?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Valid until</dt>
                    <dd class="text-gray-900">{{ $quote->valid_until?->format('Y-m-d') ?? '—' }}</dd>
                </div>
                @if ($quote->notes)
                    <div class="md:col-span-2">
                        <dt class="text-sm text-gray-500">Notes</dt>
                        <dd class="text-gray-700">{{ $quote->notes }}</dd>
                    </div>
                @endif
            </dl>

            <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-md">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit price</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($quote->items as $item)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ $item->product?->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-600">{{ $item->description ?? '—' }}</td>
                            <td class="px-4 py-2 text-sm text-right text-gray-900">{{ number_format($item->quantity, 4) }}</td>
                            <td class="px-4 py-2 text-sm text-right text-gray-900">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-right font-medium">{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4 flex justify-end">
                <p class="text-sm font-semibold text-gray-900">Total: {{ number_format($quote->total, 2) }}</p>
            </div>

            @if ($quote->status === \App\Enums\Sales\QuoteStatus::Draft)
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <a href="{{ route('sales.quotes.edit', $quote) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Edit quote</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
