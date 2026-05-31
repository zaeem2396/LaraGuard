<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Rules;

use LaravelGuard\Guard\Contracts\RuleContract;
use LaravelGuard\Guard\Support\RuleIdConvention;

/**
 * Convenience base for third-party rules with convention-based ids.
 */
abstract class AbstractRule implements RuleContract
{
    public function id(): string
    {
        return RuleIdConvention::fromClass(static::class);
    }
}
