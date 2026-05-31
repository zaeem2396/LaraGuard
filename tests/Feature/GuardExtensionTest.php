<?php

declare(strict_types=1);

use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Support\GuardAnalysisEngine;
use LaravelGuard\Guard\Support\RuleRegistry;
use LaravelGuard\Guard\Tests\Stubs\RegistersForbiddenWordRuleProvider;

beforeEach(function (): void {
    $this->app->setBasePath(__DIR__.'/../fixtures/extension-rules');

    if (! $this->app->getProvider(RegistersForbiddenWordRuleProvider::class)) {
        $this->app->register(RegistersForbiddenWordRuleProvider::class);
        $this->app->boot();
    }
});

it('registers extended rules in the rule registry', function (): void {
    $registry = app(RuleRegistry::class);
    $ruleIds = array_map(static fn ($rule) => $rule->id(), $registry->all());

    expect($ruleIds)->toContain('forbidden-word');
});

it('runs extended rules during analysis', function (): void {
    $violations = app(GuardAnalysisEngine::class)->run()->violations;

    $custom = array_values(array_filter(
        $violations,
        static fn ($v) => $v->ruleId === 'forbidden-word',
    ));

    expect($custom)->not->toBeEmpty();
    expect($custom[0]->file)->toBe('app/Services/MarkedService.php');
});

it('respects rule_options enabled flag for extended rules', function (): void {
    config([
        'guard.rule_options' => [
            'forbidden-word' => [
                'enabled' => false,
            ],
        ],
    ]);

    $this->app->forgetInstance(GuardConfig::class);
    $this->app->forgetInstance(RuleRegistry::class);
    $this->app->forgetInstance(GuardAnalysisEngine::class);

    $ruleIds = array_map(
        static fn ($rule) => $rule->id(),
        app(RuleRegistry::class)->all(),
    );

    expect($ruleIds)->not->toContain('forbidden-word');
});
