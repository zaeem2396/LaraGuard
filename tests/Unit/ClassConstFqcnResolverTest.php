<?php

declare(strict_types=1);

use LaravelGuard\Guard\Support\Binding\ClassConstFqcnResolver;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

it('resolves imported short class constants to fqcn', function (): void {
    $importMap = [
        'OrderRepository' => 'App\Repositories\OrderRepository',
    ];

    $node = new ClassConstFetch(
        new Name('OrderRepository'),
        new Identifier('class'),
    );

    expect(ClassConstFqcnResolver::resolve($node, $importMap))
        ->toBe('App\Repositories\OrderRepository');
});

it('returns null for non-class constants', function (): void {
    $node = new ClassConstFetch(
        new Name('Foo'),
        new Identifier('BAR'),
    );

    expect(ClassConstFqcnResolver::resolve($node, []))->toBeNull();
});
