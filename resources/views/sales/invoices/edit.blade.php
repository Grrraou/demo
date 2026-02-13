@extends('layouts.app')

@section('title', 'Edit invoice: ' . $invoice->number)

@section('content')
<div class="py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('sales.invoices.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Invoices</a>
        </div>

        @if (session('success'))
            <p class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</p>
        @endif
        @if ($errors->any())
            <ul class="mb-4 p-3 bg-red-100 text-red-800 rounded list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <h1 class="text-xl font-bold text-gray-900 mb-6">Edit invoice</h1>

            @php
                $invoiceItemsRows = $invoice->items->count() ? $invoice->items->map(fn ($i) => ['product_id' => $i->product_id, 'description' => $i->description, 'quantity' => $i->quantity, 'unit_price' => $i->unit_price])->toArray() : [['product_id' => '', 'description' => '', 'quantity' => '', 'unit_price' => '']];
                $invoiceItemsRows = old('items', $invoiceItemsRows);
            @endphp

            <form action="{{ route('sales.invoices.update', $invoice) }}" method="POST" class="space-y-6" x-data="invoiceForm()">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="customer_company_id" class="block text-sm font-medium text-gray-700">Customer</label>
                        <select name="customer_company_id" id="customer_company_id" required
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">— Select —</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_company_id', $invoice->customer_company_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Number</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $invoice->number }}</p>
                    </div>
                    <div>
                        <label for="invoice_date" class="block text-sm font-medium text-gray-700">Invoice date</label>
                        <input type="date" name="invoice_date" id="invoice_date" value="{{ old('invoice_date', $invoice->invoice_date?->format('Y-m-d')) }}"
                               class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700">Due date</label>
                        <input type="date" name="due_date" id="due_date" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}"
                               class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" id="notes" rows="2"
                              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $invoice->notes) }}</textarea>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium text-gray-700">Lines</label>
                        <button type="button" @click="addRow()" class="text-sm text-indigo-600 hover:text-indigo-800">+ Add line</button>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-md">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit price</th>
                                <th class="w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="invoice-items-tbody">
                            @foreach ($invoiceItemsRows as $idx => $row)
                                <tr class="invoice-item-row">
                                    <td class="px-3 py-2">
                                        <select name="items[{{ $idx }}][product_id]" class="block w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm" required>
                                            <option value="">— Select —</option>
                                            @foreach ($products as $p)
                                                <option value="{{ $p->id }}" {{ (old('items.'.$idx.'.product_id') ?? $row['product_id'] ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->sku }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="items[{{ $idx }}][description]" value="{{ old('items.'.$idx.'.description', $row['description'] ?? '') }}"
                                               class="block w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.0001" min="0" name="items[{{ $idx }}][quantity]" value="{{ old('items.'.$idx.'.quantity', $row['quantity'] ?? '') }}"
                                               class="block w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm text-right" required>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0" name="items[{{ $idx }}][unit_price]" value="{{ old('items.'.$idx.'.unit_price', $row['unit_price'] ?? '') }}"
                                               class="block w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm text-right" required>
                                    </td>
                                    <td class="px-3 py-2">
                                        <button type="button" class="text-red-600 hover:text-red-800 invoice-remove-row">×</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Save</button>
                    <a href="{{ route('sales.invoices.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function invoiceForm() {
    const products = @json($products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'sku' => $p->sku])->values());
    let idx = {{ count($invoiceItemsRows) }};
    return {
        addRow() {
            const tbody = document.getElementById('invoice-items-tbody');
            const tr = document.createElement('tr');
            tr.className = 'invoice-item-row';
            let opts = products.map(p => `<option value="${p.id}">${p.name} (${p.sku})</option>`).join('');
            tr.innerHTML = `
                <td class="px-3 py-2"><select name="items[${idx}][product_id]" class="block w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm" required><option value="">— Select —</option>${opts}</select></td>
                <td class="px-3 py-2"><input type="text" name="items[${idx}][description]" class="block w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm"></td>
                <td class="px-3 py-2"><input type="number" step="0.0001" min="0" name="items[${idx}][quantity]" class="block w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm text-right" required></td>
                <td class="px-3 py-2"><input type="number" step="0.01" min="0" name="items[${idx}][unit_price]" class="block w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm text-right" required></td>
                <td class="px-3 py-2"><button type="button" class="text-red-600 hover:text-red-800 invoice-remove-row">×</button></td>
            `;
            tbody.appendChild(tr);
            idx++;
            tr.querySelector('.invoice-remove-row').addEventListener('click', () => tr.remove());
        }
    };
}
document.querySelectorAll('.invoice-remove-row').forEach(btn => {
    btn.addEventListener('click', function() {
        const rows = document.querySelectorAll('.invoice-item-row');
        if (rows.length > 1) this.closest('tr').remove();
    });
});
</script>
@endsection
