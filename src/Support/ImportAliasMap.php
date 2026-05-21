<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

/**
 * Resolves short type names from file-level `use` imports for AST analysis.
 */
final class ImportAliasMap
{
    /**
     * @param  list<Stmt>  $statements
     * @return array<string, string> alias => fully-qualified name
     */
    public static function fromStatements(array $statements): array
    {
        $map = [];

        self::collectFromStatementList($statements, $map);

        return $map;
    }

    /**
     * @param  list<Stmt>  $statements
     * @param  array<string, string>  $map
     */
    private static function collectFromStatementList(array $statements, array &$map): void
    {
        foreach ($statements as $statement) {
            if ($statement instanceof Use_) {
                self::mergeUseStatement($statement, $map);

                continue;
            }

            if ($statement instanceof Namespace_ && $statement->stmts !== null) {
                self::collectFromStatementList($statement->stmts, $map);
            }
        }
    }

    /**
     * @param  array<string, string>  $map
     */
    private static function mergeUseStatement(Use_ $statement, array &$map): void
    {
        foreach ($statement->uses as $use) {
            if (! $use instanceof UseUse) {
                continue;
            }

            $fqcn = $use->name->toString();
            $alias = $use->alias !== null
                ? $use->alias->toString()
                : $use->name->getLast();

            $map[$alias] = $fqcn;
        }
    }

    /**
     * @param  array<string, string>  $importMap
     */
    public static function resolveName(Name $name, array $importMap): string
    {
        $parts = $name->getParts();

        if (count($parts) === 1 && isset($importMap[$parts[0]])) {
            return $importMap[$parts[0]];
        }

        return ltrim($name->toString(), '\\');
    }

    /**
     * @param  array<string, string>  $importMap
     */
    public static function resolveString(string $typeName, array $importMap): string
    {
        if (str_contains($typeName, '\\')) {
            return ltrim($typeName, '\\');
        }

        return $importMap[$typeName] ?? $typeName;
    }
}
