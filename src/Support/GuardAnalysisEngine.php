<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support;

use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Config\ViolationSeverityOverride;
use LaravelGuard\Guard\Contracts\ScannerContract;
use LaravelGuard\Guard\Violations\Violation;

final readonly class GuardAnalysisEngine
{
    public function __construct(
        private ScannerContract $scanner,
        private RuleRegistry $rules,
        private GuardConfig $config,
    ) {}

    public function run(): AnalysisResult
    {
        $outcome = $this->scanner->scan();
        /** @var list<Violation> $violations */
        $violations = $outcome->diagnostics;

        foreach ($outcome->files as $file) {
            foreach ($this->rules->all() as $rule) {
                foreach ($rule->evaluate($file) as $violation) {
                    $violations[] = ViolationSeverityOverride::apply($violation, $this->config);
                }
            }
        }

        return new AnalysisResult(
            violations: $violations,
            filesScanned: count($outcome->files),
        );
    }
}
