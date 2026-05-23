<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\TenantContext;
use App\Services\HttpTenantContext;
use Illuminate\Support\ServiceProvider;

class ScopedProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(TenantContext::class, HttpTenantContext::class);
    }
}
