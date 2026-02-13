<?php

namespace App\Enums\Sales;

enum OrderStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
}
