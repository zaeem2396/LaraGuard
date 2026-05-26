<?php

declare(strict_types=1);

use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Rules\LayerViolationRule;
use LaravelGuard\Guard\Support\GuardAnalysisEngine;
use LaravelGuard\Guard\Violations\Severity;

beforeEach(function (): void {
    $this->app->setBasePath(__DIR__.'/../fixtures/layer-violations');
});

it('flags controllers that import models directly', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $layerViolations = array_values(array_filter(
        $violations,
        static fn ($v) => $v->ruleId === app(LayerViolationRule::class)->id(),
    ));

    $files = array_map(static fn ($v) => $v->file, $layerViolations);

    expect($files)->toContain('app/Http/Controllers/IllegalModelController.php');
    expect($layerViolations[0]->severity)->toBe(Severity::Warning);
});

it('allows controllers to depend on services', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $illegal = array_values(array_filter(
        $violations,
        static fn ($v) => $v->ruleId === 'layer-violation'
            && $v->file === 'app/Http/Controllers/LegalServiceController.php',
    ));

    expect($illegal)->toBeEmpty();
});

it('allows repository to model dependencies', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $illegal = array_values(array_filter(
        $violations,
        static fn ($v) => $v->ruleId === 'layer-violation'
            && $v->file === 'app/Repositories/OrderRepository.php',
    ));

    expect($illegal)->toBeEmpty();
});

it('does nothing when layers config order is empty', function (): void {
    config([
        'guard.layers' => [
            'order' => [],
        ],
    ]);

    $this->app->forgetInstance(GuardConfig::class);
    $this->app->forgetInstance(GuardAnalysisEngine::class);
    $this->app->forgetInstance(LayerViolationRule::class);

    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $layerViolations = array_values(array_filter(
        $violations,
        static fn ($v) => $v->ruleId === 'layer-violation',
    ));

    expect($layerViolations)->toBeEmpty();
});
