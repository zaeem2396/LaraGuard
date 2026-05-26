<?php

declare(strict_types=1);

use LaravelGuard\Guard\Config\LayerArchitectureConfig;
use LaravelGuard\Guard\Support\Layers\LayerDependencyPolicy;
use LaravelGuard\Guard\Support\Layers\LayerResolver;

it('allows controller to service dependencies by default', function (): void {
    $config = LayerArchitectureConfig::fromGuardConfig([
        'layers' => [
            'order' => ['Controller', 'Service', 'Repository', 'Model'],
        ],
    ]);

    expect($config)->not->toBeNull();

    $resolver = new LayerResolver($config);
    $policy = new LayerDependencyPolicy($config, $resolver);

    expect($policy->allows('Controller', 'App\\Services\\OrderService'))->toBeTrue();
    expect($policy->allows('Controller', 'App\\Models\\User'))->toBeFalse();
    expect($policy->allows('Controller', 'Illuminate\\Http\\Request'))->toBeTrue();
});

it('honors configured layer exceptions', function (): void {
    $config = LayerArchitectureConfig::fromGuardConfig([
        'layers' => [
            'order' => ['Controller', 'Service', 'Repository', 'Model'],
        ],
        'layer_violation' => [
            'exceptions' => [
                ['from' => 'Controller', 'to_prefix' => 'App\\Legacy\\'],
            ],
        ],
    ]);

    expect($config)->not->toBeNull();

    $resolver = new LayerResolver($config);
    $policy = new LayerDependencyPolicy($config, $resolver);

    expect($policy->allows('Controller', 'App\\Legacy\\Reports\\Summary'))->toBeTrue();
});
