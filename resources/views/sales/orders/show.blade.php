@extends('layouts.app')

@section('title', 'Order ' . $order->number)

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('sales.orders.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Orders</a>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <div class="flex justify-between items-start mb-6">
                <h1 class="text-xl font-bold text-gray-900">Order {{ $order->number }}</h1>
                <span class="px-2 py-1 text-sm rounded bg-gray-100">{{ $order->status?->value ?? '—' }}</span>
            </div>

            <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
                <div>
                    <dt class="text-sm text-gray-500">Customer</dt>
                    <dd class="font-medium text-gray-900">{{ $order->customer?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Order date</dt>
                    <dd class="text-gray-900">{{ $order->order_date?->format('Y-m-d') ?? '—' }}</dd>
                </div>
                @if ($order->quote)
                    <div>
                        <dt class="text-sm text-gray-500">From quote</dt>
                        <dd><a href="{{ route('sales.quotes.show', $order->quote) }}" class="text-indigo-600 hover:text-indigo-800">{{ $order->quote->number }}</a></dd>
                    </div>
                @endif
                @if ($order->notes)
                    <div class="md:col-span-2">
                        <dt class="text-sm text-gray-500">Notes</dt>
                        <dd class="text-gray-700">{{ $order->notes }}</dd>
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
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ $item->product?->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-600">{{ $item->description ?? '—' }}</td>
                            <td class="px-4 py-2 text-sm text-right text-gray-900">{{ number_format($item->quantity, 4) }}</td>
                            <td class="px-4 py-2 text-sm text-right text-gray-900">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-right font-medium">{{ number_format((float)$item->quantity * (float)$item->unit_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4 flex justify-end">
                <p class="text-sm font-semibold text-gray-900">Total: {{ number_format($order->total ?? 0, 2) }}</p>
            </div>

            @if ($order->deliveries->isNotEmpty())
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <h2 class="text-sm font-medium text-gray-700 mb-2">Deliveries</h2>
                    <ul class="space-y-1">
                        @foreach ($order->deliveries as $d)
                            <li><a href="{{ route('sales.deliveries.show', $d) }}" class="text-indigo-600 hover:text-indigo-800">{{ $d->number }}</a> ({{ $d->status?->value }})</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if ($order->invoices->isNotEmpty())
                <div class="mt-4">
                    <h2 class="text-sm font-medium text-gray-700 mb-2">Invoices</h2>
                    <ul class="space-y-1">
                        @foreach ($order->invoices as $inv)
                            <li><a href="{{ route('sales.invoices.show', $inv) }}" class="text-indigo-600 hover:text-indigo-800">{{ $inv->number }}</a> ({{ $inv->status?->value }})</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($order->status?->value === 'draft')
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <a href="{{ route('sales.orders.edit', $order) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Edit order</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
