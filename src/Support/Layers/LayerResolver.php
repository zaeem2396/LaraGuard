<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support\Layers;

use LaravelGuard\Guard\Config\LayerArchitectureConfig;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeFinder;

final readonly class LayerResolver
{
    /** @var list<array{layer: string, prefix: string, length: int}> */
    private array $prefixIndex;

    public function __construct(
        LayerArchitectureConfig $config,
    ) {
        $index = [];

        foreach ($config->namespaces as $layer => $prefixes) {
            foreach ($prefixes as $prefix) {
                $normalized = ltrim($prefix, '\\');
                $index[] = [
                    'layer' => $layer,
                    'prefix' => $normalized,
                    'length' => strlen($normalized),
                ];
            }
        }

        usort($index, static fn (array $a, array $b): int => $b['length'] <=> $a['length']);

        $this->prefixIndex = $index;
    }

    public function layerForFqcn(string $fqcn): ?string
    {
        $normalized = ltrim($fqcn, '\\');

        foreach ($this->prefixIndex as $entry) {
            $prefix = $entry['prefix'];

            if ($normalized === $prefix || str_starts_with($normalized, $prefix.'\\')) {
                return $entry['layer'];
            }
        }

        return null;
    }

    /**
     * @param  list<Stmt>  $statements
     */
    public function layerForFile(array $statements, string $relativePath): ?string
    {
        $className = $this->resolveDeclaredClassName($statements);

        if ($className !== null) {
            $layer = $this->layerForFqcn($className);

            if ($layer !== null) {
                return $layer;
            }
        }

        $normalizedPath = str_replace('\\', '/', $relativePath);

        foreach ($this->prefixIndex as $entry) {
            $segment = str_replace('\\', '/', $entry['prefix']);

            if (str_contains($normalizedPath, str_replace('App/', 'app/', $segment).'/')
                || str_contains($normalizedPath, strtolower($segment).'/')) {
                return $entry['layer'];
            }
        }

        return null;
    }

    /**
     * @param  list<Stmt>  $statements
     */
    private function resolveDeclaredClassName(array $statements): ?string
    {
        $namespace = $this->resolveNamespace($statements);
        $finder = new NodeFinder;

        foreach ($finder->findInstanceOf($statements, Class_::class) as $class) {
            if (! $class instanceof Class_ || $class->isAnonymous() || ! $class->name instanceof Identifier) {
                continue;
            }

            $shortName = $class->name->toString();

            return $namespace !== null
                ? $namespace.'\\'.$shortName
                : $shortName;
        }

        return null;
    }

    /**
     * @param  list<Stmt>  $statements
     */
    private function resolveNamespace(array $statements): ?string
    {
        $finder = new NodeFinder;

        foreach ($finder->findInstanceOf($statements, Namespace_::class) as $namespace) {
            if ($namespace instanceof Namespace_ && $namespace->name instanceof Name) {
                return $namespace->name->toString();
            }
        }

        return null;
    }
}
