<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support;

use LaravelGuard\Guard\Violations\Violation;

/**
 * @immutable
 */
final readonly class ScanOutcome
{
    /**
     * @param  list<ScanFile>  $files
     * @param  list<Violation>  $diagnostics
     */
    public function __construct(
        public array $files,
        public array $diagnostics,
    ) {}
}
