<?php

declare(strict_types=1);

use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Config\ViolationSeverityOverride;
use LaravelGuard\Guard\Violations\Severity;
use LaravelGuard\Guard\Violations\Violation;

it('applies configured severity overrides to violations', function (): void {
    $config = GuardConfig::fromArray([
        'rule_options' => [
            'fat-class' => [
                'severity' => 'warning',
            ],
        ],
    ]);

    $violation = new Violation(
        severity: Severity::Info,
        file: 'app/Services/FatService.php',
        line: 1,
        message: 'Class is large',
        ruleId: 'fat-class',
    );

    $overridden = ViolationSeverityOverride::apply($violation, $config);

    expect($overridden->severity)->toBe(Severity::Warning);
});

it('leaves violations unchanged when no override is configured', function (): void {
    $config = GuardConfig::fromArray([]);

    $violation = new Violation(
        severity: Severity::Error,
        file: 'app/Http/Controllers/BadController.php',
        line: 10,
        message: 'Database usage',
        ruleId: 'no-db-in-controller',
    );

    $result = ViolationSeverityOverride::apply($violation, $config);

    expect($result->severity)->toBe(Severity::Error);
});
