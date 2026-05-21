<?php

declare(strict_types=1);

use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Support\Console\GuardConsoleRenderer;
use LaravelGuard\Guard\Violations\Severity;
use LaravelGuard\Guard\Violations\Violation;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

it('serializes violations for json consumers', function (): void {
    $config = new GuardConfig(
        rules: [],
        ignorePrefixes: [],
        thresholds: [],
        reportFrom: Severity::Info,
        failOn: Severity::Error,
        scanRoots: ['app'],
    );

    $output = new BufferedOutput;
    $io = new SymfonyStyle(new ArrayInput([]), $output);

    $renderer = new GuardConsoleRenderer($config, $io);

    $payload = $renderer->toJsonPayload([
        new Violation(
            severity: Severity::Warning,
            file: 'app/Example.php',
            line: 12,
            message: 'Example warning',
            suggestion: 'Resolve the warning',
            ruleId: 'example',
        ),
    ], 3);

    expect($payload['warnings'][0])->toMatchArray([
        'severity' => Severity::Warning->value,
        'file' => 'app/Example.php',
        'line' => 12,
        'message' => 'Example warning',
        'suggestion' => 'Resolve the warning',
        'rule_id' => 'example',
    ]);
});
