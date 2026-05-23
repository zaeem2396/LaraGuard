<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrderRepository;

class BoundCheckoutService
{
    public function __construct(
        private OrderRepository $orders,
    ) {}
}
