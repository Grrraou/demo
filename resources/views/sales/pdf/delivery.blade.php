<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Delivery {{ $delivery->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; margin: 24px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #6b7280; font-size: 11px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background: #f9fafb; font-weight: 600; }
        .text-right { text-align: right; }
        .notes { margin-top: 16px; color: #4b5563; font-size: 11px; }
    </style>
</head>
<body>
    <h1>Delivery {{ $delivery->number }}</h1>
    <div class="meta">
        Order: {{ $delivery->salesOrder?->number ?? '—' }} ({{ $delivery->salesOrder?->customer?->name ?? '—' }})<br>
        @if ($delivery->delivered_at) Delivered at: {{ $delivery->delivered_at->format('Y-m-d H:i') }}<br> @endif
        Status: {{ $delivery->status?->value ?? '—' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th class="text-right">Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($delivery->items as $di)
                <tr>
                    <td>{{ $di->salesOrderItem?->product?->name ?? '—' }}</td>
                    <td class="text-right">{{ number_format($di->quantity, 4) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if ($delivery->notes)
        <div class="notes">Notes: {{ $delivery->notes }}</div>
    @endif
</body>
</html>
