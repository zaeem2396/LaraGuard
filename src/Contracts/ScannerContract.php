<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Contracts;

use LaravelGuard\Guard\Support\ScanFile;

interface ScannerContract
{
    /**
     * @return list<ScanFile>
     */
    public function scan(): array;
}
