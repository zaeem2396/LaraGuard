<?php

declare(strict_types=1);

use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Support\Ignore\GuardIgnoreMatcher;
use LaravelGuard\Guard\Support\Ignore\GuardIgnoreMatcherFactory;
use LaravelGuard\Guard\Support\Ignore\IgnorePattern;

it('applies patterns in order with later matches winning', function (): void {
    $matcher = new GuardIgnoreMatcher([
        IgnorePattern::include('app/Legacy/**'),
        IgnorePattern::exclude('app/Legacy/Important.php'),
    ]);

    expect($matcher->shouldIgnore('app/Legacy/Skipped.php'))->toBeTrue();
    expect($matcher->shouldIgnore('app/Legacy/Important.php'))->toBeFalse();
    expect($matcher->shouldIgnore('app/Http/Controllers/Foo.php'))->toBeFalse();
});

it('merges config ignore prefixes with guardignore file patterns', function (): void {
    $basePath = sys_get_temp_dir().'/guard-ignore-merge-'.uniqid();
    mkdir($basePath, 0777, true);
    file_put_contents($basePath.'/.guardignore', "app/Legacy/**\n");

    $config = GuardConfig::fromArray([
        'rules' => [],
        'ignore' => ['vendor'],
        'paths' => ['app'],
    ]);

    $matcher = GuardIgnoreMatcherFactory::create($basePath, $config);

    expect($matcher->shouldIgnore('vendor/foo.php'))->toBeTrue();
    expect($matcher->shouldIgnore('app/Legacy/Foo.php'))->toBeTrue();
    expect($matcher->shouldIgnore('app/Http/Foo.php'))->toBeFalse();
});
