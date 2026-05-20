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
        $violations = [];

        foreach ($this->scanner->scan() as $file) {
            foreach ($this->rules->all() as $rule) {
                foreach ($rule->evaluate($file) as $violation) {
                    $violations[] = $violation;
                }
            }
        }

        return $violations;
    }
}
