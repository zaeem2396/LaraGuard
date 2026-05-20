<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Contracts;

use LaravelGuard\Guard\Support\ScanFile;
use LaravelGuard\Guard\Violations\Violation;

interface RuleContract
{
    public function id(): string;

    /**
     * @return list<Violation>
     */
    public function evaluate(ScanFile $file): array;
}
