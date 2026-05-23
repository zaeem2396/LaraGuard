<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support\Binding;

use LaravelGuard\Guard\Support\ImportAliasMap;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;

/**
 * Resolves `SomeClass::class` expressions to FQCN strings.
 */
final class ClassConstFqcnResolver
{
    /**
     * @param  array<string, string>  $importMap
     */
    public static function resolve(?Node $node, array $importMap): ?string
    {
        if (! $node instanceof ClassConstFetch) {
            return null;
        }

        if (! $node->name instanceof Identifier || $node->name->toString() !== 'class') {
            return null;
        }

        if ($node->class instanceof FullyQualified) {
            return ltrim($node->class->toString(), '\\');
        }

        if ($node->class instanceof Name) {
            return ImportAliasMap::resolveName($node->class, $importMap);
        }

        return null;
    }
}
