<?php

namespace App\Repositories\Sales;

use App\Models\Sales\SalesOrderItem;

class SalesOrderItemRepository
{
    public function __construct(
        private SalesOrderItem $model
    ) {}

    public function create(array $data): SalesOrderItem
    {
        return $this->model->newQuery()->create($data);
    }
}
