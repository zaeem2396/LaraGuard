<?php

declare(strict_types=1);

use LaravelGuard\Guard\Support\GuardAnalysisEngine;

it('flags classes that exceed configured method thresholds', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run();

    $fatViolations = array_values(array_filter(
        $violations,
        static fn ($v) => $v->ruleId === 'fat-class',
    ));

    expect($fatViolations)->not->toBeEmpty();
    expect($fatViolations[0]->file)->toContain('Services/FatService.php');
    expect($fatViolations[0]->message)->toContain('FatService');
});
