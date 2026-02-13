<?php

namespace App\Repositories\Sales;

use App\Models\Sales\Payment;

class PaymentRepository
{
    public function __construct(
        private Payment $model
    ) {}

    public function create(array $data): Payment
    {
        return $this->model->newQuery()->create($data);
    }
}
