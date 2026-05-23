<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\CacheStore;
use App\Services\RedisCacheStore;
use Illuminate\Support\ServiceProvider;

class SingletonProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CacheStore::class, RedisCacheStore::class);
    }
}
