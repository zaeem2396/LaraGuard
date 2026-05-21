<?php

declare(strict_types=1);

use LaravelGuard\Guard\Support\ImportAliasMap;
use PhpParser\Node\Name;
use PhpParser\ParserFactory;

it('resolves short class names from use imports', function (): void {
    $code = <<<'PHP'
<?php
use App\Models\User;
use App\Repositories\OrderRepository as Orders;

class Example {}
PHP;

    $statements = (new ParserFactory)->createForHostVersion()->parse($code);
    $map = ImportAliasMap::fromStatements($statements ?? []);

    expect($map)->toBe([
        'User' => 'App\Models\User',
        'Orders' => 'App\Repositories\OrderRepository',
    ]);

    expect(ImportAliasMap::resolveName(new Name('User'), $map))->toBe('App\Models\User');
    expect(ImportAliasMap::resolveString('Orders', $map))->toBe('App\Repositories\OrderRepository');
});

it('collects use imports declared inside a namespace block', function (): void {
    $code = <<<'PHP'
<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class Example {}
PHP;

    $statements = (new ParserFactory)->createForHostVersion()->parse($code);
    $map = ImportAliasMap::fromStatements($statements ?? []);

    expect($map)->toHaveKey('User', 'App\Models\User');
    expect($map)->toHaveKey('DB', 'Illuminate\Support\Facades\DB');
});
