<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Tests\Stubs;

use Illuminate\Support\ServiceProvider;
use LaravelGuard\Guard\Facades\Guard;
use LaravelGuard\Guard\Tests\Stubs\Rules\ForbiddenWordRule;

final class RegistersForbiddenWordRuleProvider extends ServiceProvider
{
    public function boot(): void
    {
        Guard::extend(ForbiddenWordRule::class);
    }
}
