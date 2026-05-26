<?php

declare(strict_types=1);

use LaravelGuard\Guard\Config\LayerArchitectureConfig;
use LaravelGuard\Guard\Support\Layers\LayerResolver;

it('maps namespace prefixes to layers with longest match winning', function (): void {
    $config = LayerArchitectureConfig::fromGuardConfig([
        'layers' => [
            'order' => ['Controller', 'Service', 'Repository', 'Model'],
            'namespaces' => [
                'Controller' => ['App\\Http\\Controllers'],
                'Service' => ['App\\Services'],
                'Repository' => ['App\\Repositories'],
                'Model' => ['App\\Models'],
            ],
        ],
    ]);

    expect($config)->not->toBeNull();

    $resolver = new LayerResolver($config);

    expect($resolver->layerForFqcn('App\\Http\\Controllers\\FooController'))->toBe('Controller');
    expect($resolver->layerForFqcn('App\\Services\\OrderService'))->toBe('Service');
    expect($resolver->layerForFqcn('App\\Repositories\\OrderRepository'))->toBe('Repository');
    expect($resolver->layerForFqcn('App\\Models\\User'))->toBe('Model');
    expect($resolver->layerForFqcn('Illuminate\\Support\\Collection'))->toBeNull();
});
