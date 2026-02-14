@extends('layouts.app')

@section('title', 'Delivery ' . $delivery->number)

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('sales.deliveries.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Deliveries</a>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <div class="flex justify-between items-start mb-6">
                <h1 class="text-xl font-bold text-gray-900">Delivery {{ $delivery->number }}</h1>
                <div class="flex items-center gap-2">
                    <a href="{{ route('sales.deliveries.pdf', $delivery) }}" target="_blank" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Download PDF</a>
                    <span class="px-2 py-1 text-sm rounded bg-gray-100">{{ $delivery->status?->value ?? '—' }}</span>
                </div>
            </div>

            <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
                <div>
                    <dt class="text-sm text-gray-500">Order</dt>
                    <dd>
                        @if ($delivery->salesOrder)
                            <a href="{{ route('sales.orders.show', $delivery->salesOrder) }}" class="text-indigo-600 hover:text-indigo-800">{{ $delivery->salesOrder->number }}</a>
                            ({{ $delivery->salesOrder->customer?->name }})
                        @else
                            —
                        @endif
                    </dd>
                </div>
                @if ($delivery->delivered_at)
                    <div>
                        <dt class="text-sm text-gray-500">Delivered at</dt>
                        <dd class="text-gray-900">{{ $delivery->delivered_at->format('Y-m-d H:i') }}</dd>
                    </div>
                @endif
                @if ($delivery->notes)
                    <div class="md:col-span-2">
                        <dt class="text-sm text-gray-500">Notes</dt>
                        <dd class="text-gray-700">{{ $delivery->notes }}</dd>
                    </div>
                @endif
            </dl>

            <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-md">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($delivery->items as $di)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ $di->salesOrderItem?->product?->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-sm text-right text-gray-900">{{ number_format($di->quantity, 4) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
