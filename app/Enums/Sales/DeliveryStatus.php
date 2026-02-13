<?php

namespace App\Enums\Sales;

enum DeliveryStatus: string
{
    case Pending = 'pending';
    case Partial = 'partial';
    case Delivered = 'delivered';
}
