<?php

declare(strict_types=1);

use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Facades\Guard;
use LaravelGuard\Guard\GuardManager;
use LaravelGuard\Guard\Support\RuleRegistry;
use LaravelGuard\Guard\Tests\Stubs\Rules\ForbiddenWordRule;

it('supports booting callbacks for deferred registration', function (): void {
    config([
        'guard.rules' => [],
    ]);

    $this->app->forgetInstance(GuardConfig::class);
    $this->app->forgetInstance(RuleRegistry::class);

    Guard::booting(function (): void {
        Guard::extend(ForbiddenWordRule::class);
    });

    $this->app->make(GuardManager::class)->invokeBootCallbacks();

    $ruleIds = array_map(
        static fn ($rule) => $rule->id(),
        app(RuleRegistry::class)->all(),
    );

    expect($ruleIds)->toContain('forbidden-word');
});
