<?php

declare(strict_types=1);

use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Config\InvalidGuardConfigurationException;
use LaravelGuard\Guard\Violations\Severity;

it('parses per-rule enabled flags and severity overrides', function (): void {
    $config = GuardConfig::fromArray([
        'rules' => [],
        'rule_options' => [
            'fat-class' => [
                'enabled' => false,
                'severity' => 'warning',
            ],
        ],
    ]);

    expect($config->isRuleEnabled('fat-class'))->toBeFalse();
    expect($config->isRuleEnabled('no-db-in-controller'))->toBeTrue();
    expect($config->ruleOption('fat-class')?->severity)->toBe(Severity::Warning);
});

it('throws when scan paths are explicitly empty', function (): void {
    GuardConfig::fromArray([
        'paths' => [],
    ]);
})->throws(InvalidGuardConfigurationException::class, 'guard.paths must contain at least one scan root.');

it('reads fail_on severity from config array', function (): void {
    $config = GuardConfig::fromArray([
        'severity' => [
            'fail_on' => 'warning',
        ],
    ]);

    expect($config->failOn)->toBe(Severity::Warning);
});
