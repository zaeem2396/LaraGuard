<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support;

use LaravelGuard\Guard\Contracts\ScanCacheContract;

final readonly class NullScanCache implements ScanCacheContract
{
    public function remember(string $key, \Closure $resolver): mixed
    {
        return $resolver();
    }

    public function forget(string $key): void
    {
        // Intentionally empty: baseline implementation for future cache adapters.
    }
}
