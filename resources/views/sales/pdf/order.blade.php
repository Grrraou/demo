<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Order {{ $order->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; margin: 24px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #6b7280; font-size: 11px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background: #f9fafb; font-weight: 600; }
        .text-right { text-align: right; }
        .total { font-weight: 600; margin-top: 12px; text-align: right; }
        .notes { margin-top: 16px; color: #4b5563; font-size: 11px; }
    </style>
</head>
<body>
    <h1>Order {{ $order->number }}</h1>
    <div class="meta">
        Customer: {{ $order->customer?->name ?? '—' }}<br>
        Order date: {{ $order->order_date?->format('Y-m-d') ?? '—' }}
        @if ($order->quote) &nbsp;|&nbsp; From quote: {{ $order->quote->number }} @endif
        &nbsp;|&nbsp; Status: {{ $order->status?->value ?? '—' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product?->name ?? '—' }}</td>
                    <td>{{ $item->description ?? '—' }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 4) }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format((float)$item->quantity * (float)$item->unit_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="total">Total: {{ number_format($order->total ?? 0, 2) }}</div>
    @if ($order->notes)
        <div class="notes">Notes: {{ $order->notes }}</div>
    @endif
</body>
</html>
