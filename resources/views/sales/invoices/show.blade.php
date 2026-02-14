@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->number)

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('sales.invoices.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Invoices</a>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <div class="flex justify-between items-start mb-6">
                <h1 class="text-xl font-bold text-gray-900">Invoice {{ $invoice->number }}</h1>
                <div class="flex items-center gap-2">
                    <a href="{{ route('sales.invoices.pdf', $invoice) }}" target="_blank" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Download PDF</a>
                    <span class="px-2 py-1 text-sm rounded bg-gray-100">{{ $invoice->status ? $invoice->status->value : '—' }}</span>
                </div>
            </div>

            <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
                <div>
                    <dt class="text-sm text-gray-500">Customer</dt>
                    <dd class="font-medium text-gray-900">{{ $invoice->customer?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Invoice date</dt>
                    <dd class="text-gray-900">{{ $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Due date</dt>
                    <dd class="text-gray-900">{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '—' }}</dd>
                </div>
                @if ($invoice->salesOrder)
                    <div>
                        <dt class="text-sm text-gray-500">Order</dt>
                        <dd><a href="{{ route('sales.orders.show', $invoice->salesOrder) }}" class="text-indigo-600 hover:text-indigo-800">{{ $invoice->salesOrder->number }}</a></dd>
                    </div>
                @endif
                @if ($invoice->notes)
                    <div class="md:col-span-2">
                        <dt class="text-sm text-gray-500">Notes</dt>
                        <dd class="text-gray-700">{{ $invoice->notes }}</dd>
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
                    @foreach ($invoice->items as $item)
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
            <div class="mt-4 flex justify-end gap-6">
                <p class="text-sm font-semibold text-gray-900">Total: {{ number_format($invoice->total ?? 0, 2) }}</p>
                @if ($invoice->payments->isNotEmpty())
                    <p class="text-sm text-gray-600">Paid: {{ number_format($invoice->paid_total ?? 0, 2) }}</p>
                @endif
            </div>

            @if ($invoice->payments->isNotEmpty())
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <h2 class="text-sm font-medium text-gray-700 mb-2">Payments</h2>
                    <ul class="space-y-1">
                        @foreach ($invoice->payments as $p)
                            <li class="text-sm">{{ number_format($p->amount, 2) }} – {{ $p->paid_at ? $p->paid_at->format('Y-m-d') : '' }} {{ $p->reference ? '(' . $p->reference . ')' : '' }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($invoice->status && $invoice->status->value === 'draft')
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <a href="{{ route('sales.invoices.edit', $invoice) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Edit invoice</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
