<?php

declare(strict_types=1);

use LaravelGuard\Guard\Rules\NoDbInControllerRule;
use LaravelGuard\Guard\Support\GuardAnalysisEngine;
use LaravelGuard\Guard\Violations\Severity;

it('flags database and eloquent usage inside controllers', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run();

    $dbViolations = array_values(array_filter(
        $violations,
        static fn ($v) => $v->ruleId === (new NoDbInControllerRule)->id(),
    ));

    expect($dbViolations)->not->toBeEmpty();
    expect($dbViolations[0]->severity)->toBe(Severity::Error);
    expect($dbViolations[0]->file)->toContain('Http/Controllers/BadController.php');

    $messages = array_map(static fn ($v) => $v->message, $dbViolations);

    expect($messages)->toContain('Database facades should not be used directly inside HTTP controllers.');
    expect($messages)->toContain('Eloquent models should not be queried directly inside HTTP controllers.');
});
