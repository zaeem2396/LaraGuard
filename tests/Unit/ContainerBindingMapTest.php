<?php

declare(strict_types=1);

use LaravelGuard\Guard\Support\ContainerBindingMap;

it('maps interface to concrete and resolves reverse lookups', function (): void {
    $map = ContainerBindingMap::fromArray([
        'App\Contracts\PaymentGateway' => 'App\Services\StripePaymentGateway',
    ]);

    expect($map->hasBinding('App\Contracts\PaymentGateway', 'App\Services\StripePaymentGateway'))->toBeTrue();
    expect($map->boundInterfaceForConcrete('App\Services\StripePaymentGateway'))
        ->toBe('App\Contracts\PaymentGateway');
    expect($map->isConcreteBoundInContainer('App\Services\StripePaymentGateway'))->toBeTrue();
    expect($map->isConcreteBoundInContainer('App\Services\Unknown'))->toBeFalse();
});

it('normalizes leading backslashes in binding keys', function (): void {
    $map = ContainerBindingMap::fromArray([
        '\\App\\Contracts\\CacheStore' => '\\App\\Services\\RedisCacheStore',
    ]);

    expect($map->hasBinding('App\Contracts\CacheStore', 'App\Services\RedisCacheStore'))->toBeTrue();
});
