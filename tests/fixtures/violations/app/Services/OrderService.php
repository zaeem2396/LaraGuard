<?php

declare(strict_types=1);

namespace App\Services;

class OrderService
{
    public function __construct(
        private \App\Repositories\OrderRepository $orders,
    ) {}
}
