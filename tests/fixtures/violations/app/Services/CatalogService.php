<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CatalogRepository;

class CatalogService
{
    public function __construct(
        private CatalogRepository $catalog,
    ) {}
}
