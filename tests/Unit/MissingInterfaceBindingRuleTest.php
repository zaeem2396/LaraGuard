<?php

declare(strict_types=1);

use LaravelGuard\Guard\Rules\MissingInterfaceBindingRule;
use LaravelGuard\Guard\Support\GuardAnalysisEngine;

it('suggests interfaces for concrete constructor dependencies in services', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $bindingViolations = array_values(array_filter(
        $violations,
        static fn ($v) => $v->ruleId === app(MissingInterfaceBindingRule::class)->id(),
    ));

    expect($bindingViolations)->not->toBeEmpty();
    expect($bindingViolations[0]->file)->toContain('Services/OrderService.php');
    expect($bindingViolations[0]->message)->toContain('OrderRepository');
});

it('flags concrete types on promoted readonly constructor properties', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $promotedViolations = array_values(array_filter(
        $violations,
        static fn ($v) => str_contains($v->file, 'PromotedOrderService.php'),
    ));

    expect($promotedViolations)->not->toBeEmpty();
    expect($promotedViolations[0]->message)->toContain('OrderRepository');
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
