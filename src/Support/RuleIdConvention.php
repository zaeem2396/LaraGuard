<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support;

use Illuminate\Support\Str;

final class RuleIdConvention
{
    /**
     * Derive a stable rule id from a rule class name.
     *
     * Examples: ForbiddenWordRule → forbidden-word, AcmeLayerRule → acme-layer
     */
    public static function fromClass(string $class): string
    {
        $basename = class_basename($class);

        if (str_ends_with($basename, 'Rule')) {
            $basename = substr($basename, 0, -4);
        }

        return Str::kebab($basename);
    }
}
