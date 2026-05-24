<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Console\GuardCommand;
use LaravelGuard\Guard\Rules\NoDbInControllerRule;
use LaravelGuard\Guard\Support\GuardAnalysisEngine;
use LaravelGuard\Guard\Support\RuleRegistry;

it('does not register disabled rules in the rule registry', function (): void {
    config([
        'guard.rule_options' => [
            'no-db-in-controller' => [
                'enabled' => false,
            ],
            'fat-class' => [
                'enabled' => true,
            ],
            'missing-interface-binding' => [
                'enabled' => true,
            ],
        ],
    ]);

    $this->app->forgetInstance(GuardConfig::class);
    $this->app->forgetInstance(RuleRegistry::class);
    $this->app->forgetInstance(NoDbInControllerRule::class);
    $this->app->forgetInstance(GuardAnalysisEngine::class);

    $registry = app(RuleRegistry::class);
    $ruleIds = array_map(static fn ($rule) => $rule->id(), $registry->all());

    expect($ruleIds)->not->toContain('no-db-in-controller');
    expect($ruleIds)->toContain('fat-class');
});

it('does not emit violations from disabled rules during analysis', function (): void {
    config([
        'guard.rule_options' => [
            'no-db-in-controller' => [
                'enabled' => false,
            ],
            'fat-class' => [
                'enabled' => true,
            ],
            'missing-interface-binding' => [
                'enabled' => true,
            ],
        ],
    ]);

    $this->app->forgetInstance(GuardConfig::class);
    $this->app->forgetInstance(RuleRegistry::class);
    $this->app->forgetInstance(NoDbInControllerRule::class);
    $this->app->forgetInstance(GuardAnalysisEngine::class);

    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $dbViolations = array_values(array_filter(
        $violations,
        static fn ($v) => $v->ruleId === 'no-db-in-controller',
    ));

    expect($dbViolations)->toBeEmpty();
});

it('allows guard command to succeed when only disabled rules would fail', function (): void {
    config([
        'guard.rule_options' => [
            'no-db-in-controller' => [
                'enabled' => false,
            ],
            'fat-class' => [
                'enabled' => true,
            ],
            'missing-interface-binding' => [
                'enabled' => true,
            ],
        ],
    ]);

    $this->app->forgetInstance(GuardConfig::class);
    $this->app->forgetInstance(RuleRegistry::class);
    $this->app->forgetInstance(GuardAnalysisEngine::class);
    $this->app->forgetInstance(GuardCommand::class);

    expect(Artisan::call('guard', [
        '--format' => 'json',
        '--fail-on-error' => true,
    ]))->toBe(0);
});
