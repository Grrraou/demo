@extends('layouts.app')

@section('title', 'New delivery')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('sales.deliveries.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Deliveries</a>
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
            <h1 class="text-xl font-bold text-gray-900 mb-6">New delivery</h1>

            <form action="{{ route('sales.deliveries.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="sales_order_id" class="block text-sm font-medium text-gray-700">Order</label>
                    <select name="sales_order_id" id="sales_order_id" required
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            onchange="window.location = this.value ? '{{ route('sales.deliveries.create') }}?order_id=' + this.value : '{{ route('sales.deliveries.create') }}'">
                        <option value="">— Select order —</option>
                        @foreach ($orders as $o)
                            <option value="{{ $o->id }}" {{ old('sales_order_id', $order?->id) == $o->id ? 'selected' : '' }}>{{ $o->number }} – {{ $o->customer?->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($order)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quantities to deliver</label>
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-md">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Ordered</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $item->product?->name ?? '—' }}</td>
                                        <td class="px-4 py-2 text-sm text-right text-gray-600">{{ number_format($item->quantity, 4) }}</td>
                                        <td class="px-4 py-2">
                                            <input type="hidden" name="items[{{ $loop->index }}][sales_order_item_id]" value="{{ $item->id }}">
                                            <input type="number" step="0.0001" min="0" max="{{ $item->quantity }}" name="items[{{ $loop->index }}][quantity]" value="{{ old('items.'.$loop->index.'.quantity', $item->quantity) }}"
                                                   class="block w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm text-right" required>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Select an order to enter delivery quantities.</p>
                @endif

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" id="notes" rows="2"
                              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700" {{ ! $order ? 'disabled' : '' }}>Create delivery</button>
                    <a href="{{ route('sales.deliveries.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
