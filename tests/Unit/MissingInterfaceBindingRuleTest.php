<?php

declare(strict_types=1);

use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Rules\MissingInterfaceBindingRule;
use LaravelGuard\Guard\Scanners\ServiceProviderBindingScanner;
use LaravelGuard\Guard\Support\ContainerBindingMap;
use LaravelGuard\Guard\Support\GuardAnalysisEngine;

it('suggests interfaces for unbound concrete constructor dependencies', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $catalogViolations = array_values(array_filter(
        $violations,
        static fn ($v) => $v->ruleId === app(MissingInterfaceBindingRule::class)->id()
            && str_contains($v->file, 'CatalogService.php'),
    ));

    expect($catalogViolations)->not->toBeEmpty();
    expect($catalogViolations[0]->message)->toContain('CatalogRepository');
});

it('suppresses violations when the concrete is bound to an interface in a provider', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $boundViolations = array_values(array_filter(
        $violations,
        static fn ($v) => str_contains($v->file, 'BoundCheckoutService.php'),
    ));

    expect($boundViolations)->toBeEmpty();
});

it('does not flag order service when repository is bound in a provider', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $orderViolations = array_values(array_filter(
        $violations,
        static fn ($v) => str_contains($v->file, 'OrderService.php')
            && ! str_contains($v->file, 'PromotedOrderService.php'),
    ));

    expect($orderViolations)->toBeEmpty();
});

it('flags concrete types on promoted readonly constructor properties', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $promotedViolations = array_values(array_filter(
        $violations,
        static fn ($v) => str_contains($v->file, 'PromotedOrderService.php'),
    ));

    expect($promotedViolations)->not->toBeEmpty();
    expect($promotedViolations[0]->message)->toContain('CatalogRepository');
});

it('does not flag constructor parameters that already use an interface', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $interfaceViolations = array_values(array_filter(
        $violations,
        static fn ($v) => str_contains($v->file, 'InterfaceBoundService.php'),
    ));

    expect($interfaceViolations)->toBeEmpty();
});

it('does not flag Illuminate or Laravel framework types', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $frameworkViolations = array_values(array_filter(
        $violations,
        static fn ($v) => str_contains($v->file, 'FrameworkCacheService.php'),
    ));

    expect($frameworkViolations)->toBeEmpty();
});

it('flags strict violations when concrete is bound but constructor still type-hints concrete', function (): void {
    config(['guard.missing_interface_binding.strict' => true]);

    $this->app->forgetInstance(GuardConfig::class);
    $this->app->forgetInstance(ContainerBindingMap::class);
    $this->app->forgetInstance(ServiceProviderBindingScanner::class);
    $this->app->forgetInstance(MissingInterfaceBindingRule::class);
    $this->app->forgetInstance(GuardAnalysisEngine::class);

    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $strictViolations = array_values(array_filter(
        $violations,
        static fn ($v) => str_contains($v->file, 'BoundCheckoutService.php')
            && str_contains($v->message, 'type-hints concrete'),
    ));

    expect($strictViolations)->not->toBeEmpty();
    expect($strictViolations[0]->message)->toContain('OrderRepositoryInterface');
});
