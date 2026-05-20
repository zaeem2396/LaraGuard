<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Contracts;

/**
 * Cache boundary for future incremental / warmed analysis (e.g. hashing file contents).
 */
interface ScanCacheContract
{
    public function remember(string $key, \Closure $resolver): mixed;

    public function forget(string $key): void;
}
