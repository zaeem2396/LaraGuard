<?php

declare(strict_types=1);

use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Rules\NoDbInControllerRule;
use LaravelGuard\Guard\Support\GuardAnalysisEngine;
use LaravelGuard\Guard\Violations\Severity;

it('flags database and eloquent usage inside controllers', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $dbViolations = array_values(array_filter(
        $violations,
        static fn ($v) => $v->ruleId === app(NoDbInControllerRule::class)->id(),
    ));

    expect($dbViolations)->not->toBeEmpty();

    $badControllerViolations = array_values(array_filter(
        $dbViolations,
        static fn ($v) => str_contains($v->file, 'BadController.php'),
    ));

    expect($badControllerViolations)->not->toBeEmpty();
    expect($badControllerViolations[0]->severity)->toBe(Severity::Error);

    $messages = array_map(static fn ($v) => $v->message, $dbViolations);

    expect($messages)->toContain('Database facades should not be used directly inside HTTP controllers.');
    expect($messages)->toContain('Eloquent models should not be queried directly inside HTTP controllers.');
});

it('flags Model::query on an imported model alias', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $queryViolations = array_values(array_filter(
        $violations,
        static fn ($v) => $v->file === 'app/Http/Controllers/BadController.php'
            && str_contains($v->message, 'Eloquent'),
    ));

    expect($queryViolations)->not->toBeEmpty();
});

it('skips controllers under configured exclude path prefixes', function (): void {
    config([
        'guard.no_db_in_controller.exclude_path_prefixes' => [
            'app/Http/Controllers/Api/V1',
        ],
    ]);

    $this->app->forgetInstance(GuardConfig::class);
    $this->app->forgetInstance(NoDbInControllerRule::class);
    $this->app->forgetInstance(GuardAnalysisEngine::class);

    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $legacyViolations = array_values(array_filter(
        $violations,
        static fn ($v) => str_contains($v->file, 'Api/V1/LegacyDbController.php'),
    ));

    expect($legacyViolations)->toBeEmpty();

    $badControllerViolations = array_values(array_filter(
        $violations,
        static fn ($v) => str_contains($v->file, 'BadController.php')
            && $v->ruleId === app(NoDbInControllerRule::class)->id(),
    ));

    expect($badControllerViolations)->not->toBeEmpty();
});
