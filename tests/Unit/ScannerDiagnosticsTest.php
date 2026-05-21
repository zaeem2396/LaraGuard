<?php

declare(strict_types=1);

use LaravelGuard\Guard\Support\GuardAnalysisEngine;

it('reports parse failures as scanner diagnostics', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $parseViolations = array_values(array_filter(
        $violations,
        static fn ($v) => $v->ruleId === 'scanner' && str_contains($v->message, 'Unable to parse'),
    ));

    expect($parseViolations)->not->toBeEmpty();
    expect($parseViolations[0]->file)->toBe('app/syntax-broken.php');
});
