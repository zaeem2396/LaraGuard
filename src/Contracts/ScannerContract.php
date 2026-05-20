<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Contracts;

use LaravelGuard\Guard\Support\ScanOutcome;

interface ScannerContract
{
    public function scan(): ScanOutcome;
}
