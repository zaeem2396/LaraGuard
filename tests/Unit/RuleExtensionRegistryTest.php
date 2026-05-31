<?php

declare(strict_types=1);

use LaravelGuard\Guard\Support\RuleExtensionRegistry;
use LaravelGuard\Guard\Tests\Stubs\Rules\ForbiddenWordRule;

it('stores valid rule classes and rejects invalid entries', function (): void {
    $registry = new RuleExtensionRegistry;

    $registry->add(ForbiddenWordRule::class);

    expect($registry->all())->toBe([ForbiddenWordRule::class]);

    $registry->add(ForbiddenWordRule::class);

    expect($registry->all())->toHaveCount(1);
});

it('throws when a rule class does not exist', function (): void {
    $registry = new RuleExtensionRegistry;

    $registry->add('App\\Rules\\MissingRule');
})->throws(InvalidArgumentException::class);

it('throws when a class does not implement RuleContract', function (): void {
    $registry = new RuleExtensionRegistry;

    $registry->add(stdClass::class);
})->throws(InvalidArgumentException::class);
