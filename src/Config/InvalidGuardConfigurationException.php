<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Config;

use RuntimeException;

final class InvalidGuardConfigurationException extends RuntimeException
{
    public static function emptyScanPaths(): self
    {
        return new self('guard.paths must contain at least one scan root.');
    }

    public static function invalidEnvScanPaths(): self
    {
        return new self('GUARD_PATHS must contain at least one comma-separated path when set.');
    }
}
