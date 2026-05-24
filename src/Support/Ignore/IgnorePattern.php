<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support\Ignore;

final readonly class IgnorePattern
{
    public function __construct(
        public string $pattern,
        public bool $negated = false,
    ) {}

    public static function include(string $pattern): self
    {
        return new self(pattern: $pattern, negated: false);
    }

    public static function exclude(string $pattern): self
    {
        return new self(pattern: $pattern, negated: true);
    }
}
