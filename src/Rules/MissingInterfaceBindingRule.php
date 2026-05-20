<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Rules;

use LaravelGuard\Guard\Contracts\RuleContract;
use LaravelGuard\Guard\Support\ScanFile;

/**
 * Reserved hook for verifying container bindings against constructor interfaces.
 *
 * Future versions will combine service provider metadata with reflection graphs.
 */
final readonly class MissingInterfaceBindingRule implements RuleContract
{
    public function id(): string
    {
        return 'missing-interface-binding';
    }

    public function evaluate(ScanFile $file): array
    {
        if ($file->relativePath === '') {
            return [];
        }

        return [];
    }
}
