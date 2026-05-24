<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Config;

use LaravelGuard\Guard\Violations\Severity;

final readonly class RuleOption
{
    public function __construct(
        public bool $enabled = true,
        public ?Severity $severity = null,
    ) {}
}
