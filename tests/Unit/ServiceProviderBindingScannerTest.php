<?php

declare(strict_types=1);

use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Scanners\ServiceProviderBindingScanner;

it('collects bind singleton and scoped container registrations', function (): void {
    $this->app->setBasePath(__DIR__.'/../fixtures/provider-bindings');

    $this->app->forgetInstance(GuardConfig::class);
    $this->app->forgetInstance(ServiceProviderBindingScanner::class);

    $map = $this->app->make(ServiceProviderBindingScanner::class)->scan();

    expect($map->hasBinding('App\Contracts\PaymentGateway', 'App\Services\StripePaymentGateway'))->toBeTrue();
    expect($map->hasBinding('App\Contracts\CacheStore', 'App\Services\RedisCacheStore'))->toBeTrue();
    expect($map->hasBinding('App\Contracts\TenantContext', 'App\Services\HttpTenantContext'))->toBeTrue();
});
