<?php

declare(strict_types=1);

use LaravelGuard\Guard\Rules\NoDbInControllerRule;
use LaravelGuard\Guard\Support\GuardAnalysisEngine;

beforeEach(function (): void {
    $this->app->setBasePath(__DIR__.'/../fixtures/guardignore-violations');
});

it('never scans paths matched by guardignore patterns', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $dbViolations = array_values(array_filter(
        $violations,
        static fn ($v) => $v->ruleId === app(NoDbInControllerRule::class)->id(),
    ));

    $files = array_map(static fn ($v) => $v->file, $dbViolations);

    expect($files)->toContain('app/Http/Controllers/ScannedDbController.php');
    expect($files)->not->toContain('app/Http/Controllers/Legacy/LegacyDbController.php');
});
