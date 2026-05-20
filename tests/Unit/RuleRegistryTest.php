<?php

declare(strict_types=1);

use LaravelGuard\Guard\Contracts\RuleContract;
use LaravelGuard\Guard\Support\RuleRegistry;

it('registers every configured rule implementation', function (): void {
    /** @var RuleRegistry $registry */
    $registry = app(RuleRegistry::class);

    $rules = $registry->all();

    expect($rules)->not->toBeEmpty();

    foreach ($rules as $rule) {
        expect($rule)->toBeInstanceOf(RuleContract::class);
    }
});
