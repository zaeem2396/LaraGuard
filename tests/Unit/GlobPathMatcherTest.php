<?php

declare(strict_types=1);

use LaravelGuard\Guard\Support\Ignore\GlobPathMatcher;

it('matches literal path prefixes', function (): void {
    expect(GlobPathMatcher::matches('app/Legacy', 'app/Legacy/Foo.php'))->toBeTrue();
    expect(GlobPathMatcher::matches('app/Legacy', 'app/Legacy'))->toBeTrue();
    expect(GlobPathMatcher::matches('app/Legacy', 'app/Http/Legacy.php'))->toBeFalse();
});

it('matches directory patterns ending with slash', function (): void {
    expect(GlobPathMatcher::matches('app/Legacy/', 'app/Legacy/Foo.php'))->toBeTrue();
    expect(GlobPathMatcher::matches('app/Legacy/', 'app/LegacyExtra/Foo.php'))->toBeFalse();
});

it('matches single-segment glob patterns', function (): void {
    expect(GlobPathMatcher::matches('app/*/Controllers', 'app/Http/Controllers/Foo.php'))->toBeTrue();
    expect(GlobPathMatcher::matches('app/*/Controllers', 'app/Api/Controllers/Foo.php'))->toBeTrue();
    expect(GlobPathMatcher::matches('app/*/Controllers', 'app/Http/Services/Foo.php'))->toBeFalse();
});

it('matches recursive glob patterns', function (): void {
    expect(GlobPathMatcher::matches('app/Legacy/**', 'app/Legacy/Foo.php'))->toBeTrue();
    expect(GlobPathMatcher::matches('app/Legacy/**', 'app/Legacy/Nested/Foo.php'))->toBeTrue();
    expect(GlobPathMatcher::matches('app/Legacy/**', 'app/Http/Legacy/Foo.php'))->toBeFalse();
});

it('matches anchored patterns from project root', function (): void {
    expect(GlobPathMatcher::matches('/app/Legacy/**', 'app/Legacy/Foo.php'))->toBeTrue();
    expect(GlobPathMatcher::matches('/app/Legacy/**', 'vendor/app/Legacy/Foo.php'))->toBeFalse();
});

it('normalizes backslashes in paths', function (): void {
    expect(GlobPathMatcher::matches('app/Legacy/**', 'app\\Legacy\\Foo.php'))->toBeTrue();
});
