<?php

declare(strict_types=1);

use LaravelGuard\Guard\Support\Ignore\GuardIgnoreFileParser;

it('parses include and negated patterns from file contents', function (): void {
    $patterns = GuardIgnoreFileParser::parseContents(<<<'TXT'
# legacy code
app/Legacy/**

!app/Legacy/Important.php
TXT);

    expect($patterns)->toHaveCount(2);
    expect($patterns[0]->pattern)->toBe('app/Legacy/**');
    expect($patterns[0]->negated)->toBeFalse();
    expect($patterns[1]->pattern)->toBe('app/Legacy/Important.php');
    expect($patterns[1]->negated)->toBeTrue();
});

it('returns an empty list when the ignore file is missing', function (): void {
    expect(GuardIgnoreFileParser::parseFile('/tmp/guard-ignore-missing-'.uniqid()))->toBe([]);
});

it('skips blank lines and comment-only lines', function (): void {
    $patterns = GuardIgnoreFileParser::parseContents(<<<'TXT'

   # comment

storage/**

TXT);

    expect($patterns)->toHaveCount(1);
    expect($patterns[0]->pattern)->toBe('storage/**');
});
