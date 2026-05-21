<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrderRepositoryInterface;

class InterfaceBoundService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
    ) {}
}
