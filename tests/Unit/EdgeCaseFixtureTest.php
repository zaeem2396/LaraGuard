<?php

declare(strict_types=1);

use LaravelGuard\Guard\Support\GuardAnalysisEngine;

it('parses empty classes, enums, and trait-composed services without scanner failures', function (): void {
    $result = app(GuardAnalysisEngine::class)->run();

    $scannerFailures = array_values(array_filter(
        $result->violations,
        static fn ($v) => $v->ruleId === 'scanner'
            && (
                str_contains($v->file, 'EmptyMarkerService')
                || str_contains($v->file, 'OrderStatus')
                || str_contains($v->file, 'TraitComposedService')
                || str_contains($v->file, 'LogsActivity')
            ),
    ));

    expect($scannerFailures)->toBeEmpty();
    expect($result->filesScanned)->toBeGreaterThan(10);
});

it('does not flag empty marker classes as fat-class violations', function (): void {
    $result = app(GuardAnalysisEngine::class)->run();

    $emptyClassViolations = array_values(array_filter(
        $result->violations,
        static fn ($v) => $v->ruleId === 'fat-class'
            && str_contains($v->file, 'EmptyMarkerService.php'),
    ));

    expect($emptyClassViolations)->toBeEmpty();
});
