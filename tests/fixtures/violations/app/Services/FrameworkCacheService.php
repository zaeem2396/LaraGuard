<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Cache\Repository;

class FrameworkCacheService
{
    public function __construct(
        private readonly Repository $cache,
    ) {}
}
