<?php

declare(strict_types=1);

use LaravelGuard\Guard\Scanners\ServiceProviderBindingScanner;
use LaravelGuard\Guard\Support\ContainerBindingMap;

it('resolves container binding map from the application container during analysis', function (): void {
    $map = app(ContainerBindingMap::class);

    expect($map)->toBeInstanceOf(ContainerBindingMap::class);
    expect($map->hasBinding('App\Repositories\OrderRepositoryInterface', 'App\Repositories\OrderRepository'))->toBeTrue();
});

it('rebuilds binding map when scanner is invoked directly', function (): void {
    $map = app(ServiceProviderBindingScanner::class)->scan();

    expect($map->boundInterfaceForConcrete('App\Repositories\OrderRepository'))
        ->toBe('App\Repositories\OrderRepositoryInterface');
});
