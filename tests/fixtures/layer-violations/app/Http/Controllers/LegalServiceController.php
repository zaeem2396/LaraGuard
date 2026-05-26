<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\OrderService;

class LegalServiceController
{
    public function __construct(
        private OrderService $orders,
    ) {}
}
