<?php

declare(strict_types=1);

use LaravelGuard\Guard\Rules\NoDbInControllerRule;
use LaravelGuard\Guard\Support\GuardAnalysisEngine;

beforeEach(function (): void {
    $this->app->setBasePath(__DIR__.'/../fixtures/guardignore-negation');
});

it('re-includes negated paths after a broader ignore rule', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $dbViolations = array_values(array_filter(
        $violations,
        static fn ($v) => $v->ruleId === app(NoDbInControllerRule::class)->id(),
    ));

    $files = array_map(static fn ($v) => $v->file, $dbViolations);

    expect($files)->toContain('app/Http/Controllers/Legacy/ImportantController.php');
    expect($files)->not->toContain('app/Http/Controllers/Legacy/SkippedController.php');
});
