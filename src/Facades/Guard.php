<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Facades;

use Illuminate\Support\Facades\Facade;
use LaravelGuard\Guard\GuardManager;

/**
 * @method static void extend(string $ruleClass)
 * @method static void booting(callable $callback)
 *
 * @see GuardManager
 */
final class Guard extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return GuardManager::class;
    }
}
