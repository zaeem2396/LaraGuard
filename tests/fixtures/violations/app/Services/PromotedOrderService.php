<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CatalogRepository;

class PromotedOrderService
{
    public function __construct(
        private readonly CatalogRepository $catalog,
    ) {}
}
