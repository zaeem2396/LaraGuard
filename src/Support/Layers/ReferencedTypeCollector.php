<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support\Layers;

use LaravelGuard\Guard\Support\ImportAliasMap;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node\UnionType;
use PhpParser\NodeFinder;

final class ReferencedTypeCollector
{
    /**
     * @param  list<Stmt>  $statements
     * @return list<array{fqcn: string, line: int}>
     */
    public static function collect(array $statements): array
    {
        $importMap = ImportAliasMap::fromStatements($statements);
        $references = [];
        $finder = new NodeFinder;

        foreach ($finder->findInstanceOf($statements, Use_::class) as $use) {
            if (! $use instanceof Use_) {
                continue;
            }

            foreach ($use->uses as $useUse) {
                if (! $useUse instanceof UseUse) {
                    continue;
                }

                $references[] = [
                    'fqcn' => ltrim($useUse->name->toString(), '\\'),
                    'line' => $useUse->getStartLine(),
                ];
            }
        }

        foreach ($finder->findInstanceOf($statements, Class_::class) as $class) {
            if (! $class instanceof Class_ || $class->isAnonymous()) {
                continue;
            }

            if ($class->extends instanceof Name) {
                self::pushReference($references, $class->extends, $importMap, $class->extends->getStartLine());
            }

            foreach ($class->implements as $interface) {
                if ($interface instanceof Name) {
                    self::pushReference($references, $interface, $importMap, $interface->getStartLine());
                }
            }

            foreach ($class->getMethods() as $method) {
                if (! $method instanceof ClassMethod) {
                    continue;
                }

                if ($method->returnType !== null) {
                    self::pushTypeNode($references, $method->returnType, $importMap, $method->getStartLine());
                }

                foreach ($method->getParams() as $parameter) {
                    if ($parameter->type !== null) {
                        self::pushTypeNode($references, $parameter->type, $importMap, $parameter->getStartLine());
                    }
                }
            }

            foreach ($class->getProperties() as $property) {
                if (! $property instanceof Property) {
                    continue;
                }

                if ($property->type !== null) {
                    self::pushTypeNode($references, $property->type, $importMap, $property->getStartLine());
                }
            }
        }

        return self::deduplicate($references);
    }

    /**
     * @param  list<array{fqcn: string, line: int}>  $references
     * @param  array<string, string>  $importMap
     */
    private static function pushReference(array &$references, Name $name, array $importMap, int $line): void
    {
        $fqcn = self::resolveName($name, $importMap);

        if ($fqcn !== null) {
            $references[] = ['fqcn' => $fqcn, 'line' => $line];
        }
    }

    /**
     * @param  list<array{fqcn: string, line: int}>  $references
     * @param  array<string, string>  $importMap
     */
    private static function pushTypeNode(array &$references, Node $type, array $importMap, int $line): void
    {
        if ($type instanceof NullableType) {
            self::pushTypeNode($references, $type->type, $importMap, $line);

            return;
        }

        if ($type instanceof UnionType) {
            foreach ($type->types as $unionType) {
                self::pushTypeNode($references, $unionType, $importMap, $line);
            }

            return;
        }

        if ($type instanceof Name) {
            self::pushReference($references, $type, $importMap, $line);
        }
    }

    /**
     * @param  array<string, string>  $importMap
     */
    private static function resolveName(Name $name, array $importMap): ?string
    {
        if ($name instanceof FullyQualified) {
            return ltrim($name->toString(), '\\');
        }

        $resolved = ImportAliasMap::resolveName($name, $importMap);

        if (! str_contains($resolved, '\\') && ! isset($importMap[$resolved])) {
            return null;
        }

        return ltrim($resolved, '\\');
    }

    /**
     * @param  list<array{fqcn: string, line: int}>  $references
     * @return list<array{fqcn: string, line: int}>
     */
    private static function deduplicate(array $references): array
    {
        $seen = [];
        $unique = [];

        foreach ($references as $reference) {
            $key = $reference['fqcn'].'@'.$reference['line'];

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $unique[] = $reference;
        }

        return $unique;
    }
}
