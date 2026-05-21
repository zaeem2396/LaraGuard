<?php

declare(strict_types=1);

use LaravelGuard\Guard\Support\GuardAnalysisEngine;

it('flags classes that exceed configured method thresholds', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $fatViolations = array_values(array_filter(
        $violations,
        static fn ($v) => $v->ruleId === 'fat-class' && str_contains($v->message, 'methods'),
    ));

    expect($fatViolations)->not->toBeEmpty();
    expect($fatViolations[0]->file)->toContain('Services/FatService.php');
    expect($fatViolations[0]->message)->toContain('FatService');
});

it('flags classes that exceed configured line span thresholds', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $lineViolations = array_values(array_filter(
        $violations,
        static fn ($v) => $v->ruleId === 'fat-class' && str_contains($v->message, 'spans'),
    ));

    expect($lineViolations)->not->toBeEmpty();
    expect($lineViolations[0]->file)->toContain('Services/LongBodyService.php');
    expect($lineViolations[0]->message)->toContain('LongBodyService');
});
