@extends('layouts.app')

@section('title', 'New invoice')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('sales.invoices.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Invoices</a>
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
            <h1 class="text-xl font-bold text-gray-900 mb-6">New invoice</h1>

            <form action="{{ route('sales.invoices.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="sales_order_id" class="block text-sm font-medium text-gray-700">Order</label>
                    <select name="sales_order_id" id="sales_order_id" required
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— Select order —</option>
                        @foreach ($orders as $o)
                            <option value="{{ $o->id }}" {{ old('sales_order_id', isset($order) ? $order->id : null) == $o->id ? 'selected' : '' }}>{{ $o->number }} – {{ $o->customer?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="invoice_date" class="block text-sm font-medium text-gray-700">Invoice date</label>
                    <input type="date" name="invoice_date" id="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}"
                           class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700">Due date</label>
                    <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}"
                           class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" id="notes" rows="2"
                              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Create invoice</button>
                    <a href="{{ route('sales.invoices.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
