<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support;

use LaravelGuard\Guard\Violations\Violation;

final readonly class AnalysisResult
{
    /**
     * @param  list<Violation>  $violations
     */
    public function __construct(
        public array $violations,
        public int $filesScanned,
    ) {}
}
