<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support;

use LaravelGuard\Guard\Contracts\ScannerContract;
use LaravelGuard\Guard\Violations\Violation;

final readonly class GuardAnalysisEngine
{
    public function __construct(
        private ScannerContract $scanner,
        private RuleRegistry $rules,
    ) {}

    /**
     * @return list<Violation>
     */
    public function run(): array
    {
        $outcome = $this->scanner->scan();
        $violations = $outcome->diagnostics;

        foreach ($outcome->files as $file) {
            foreach ($this->rules->all() as $rule) {
                foreach ($rule->evaluate($file) as $violation) {
                    $violations[] = $violation;
                }
            }
        }

        return $violations;
    }
}
