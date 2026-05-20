<?php

declare(strict_types=1);

use LaravelGuard\Guard\Rules\MissingInterfaceBindingRule;
use LaravelGuard\Guard\Support\GuardAnalysisEngine;

it('suggests interfaces for concrete constructor dependencies in services', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run();

    $bindingViolations = array_values(array_filter(
        $violations,
        static fn ($v) => $v->ruleId === (new MissingInterfaceBindingRule)->id(),
    ));

    expect($bindingViolations)->not->toBeEmpty();
    expect($bindingViolations[0]->file)->toContain('Services/OrderService.php');
    expect($bindingViolations[0]->message)->toContain('OrderRepository');
});
