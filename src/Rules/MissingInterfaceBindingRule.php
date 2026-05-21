<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Rules;

use LaravelGuard\Guard\Contracts\RuleContract;
use LaravelGuard\Guard\Support\ImportAliasMap;
use LaravelGuard\Guard\Support\ScanFile;
use LaravelGuard\Guard\Violations\Severity;
use LaravelGuard\Guard\Violations\Violation;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;

final readonly class MissingInterfaceBindingRule implements RuleContract
{
    private const array WATCHED_SEGMENTS = [
        'Services',
        'Repositories',
    ];

    public function id(): string
    {
        return 'missing-interface-binding';
    }

    public function evaluate(ScanFile $file): array
    {
        if (! $this->isWatchedPath($file->relativePath)) {
            return [];
        }

        $importMap = ImportAliasMap::fromStatements($file->statements);
        $finder = new NodeFinder;
        $violations = [];

        foreach ($finder->findInstanceOf($file->statements, Class_::class) as $class) {
            if (! $class instanceof Class_ || $class->isAnonymous() || $class->isAbstract()) {
                continue;
            }

            $constructor = $this->resolveConstructor($class);

            if ($constructor === null) {
                continue;
            }

            foreach ($constructor->getParams() as $parameter) {
                $typeName = $this->resolveParameterTypeName($parameter->type, $importMap);

                if ($typeName === null) {
                    continue;
                }

                if ($this->shouldSuggestInterface($typeName)) {
                    $violations[] = new Violation(
                        severity: Severity::Info,
                        file: $file->relativePath,
                        line: $parameter->getStartLine() ?? $constructor->getStartLine(),
                        message: sprintf(
                            'Constructor depends on concrete "%s"; prefer an interface bound in the service container.',
                            $typeName,
                        ),
                        suggestion: 'Introduce an interface (e.g. '.$typeName.'Interface) and bind it in a service provider.',
                        ruleId: $this->id(),
                    );
                }
            }
        }

        return $violations;
    }

    /**
     * @param  array<string, string>  $importMap
     */
    private function resolveParameterTypeName(?Node $type, array $importMap): ?string
    {
        if ($type instanceof NullableType) {
            return $this->resolveParameterTypeName($type->type, $importMap);
        }

        if ($type instanceof FullyQualified) {
            return ltrim($type->toString(), '\\');
        }

        if ($type instanceof Name) {
            return ImportAliasMap::resolveName($type, $importMap);
        }

        return null;
    }

    private function resolveConstructor(Class_ $class): ?ClassMethod
    {
        foreach ($class->getMethods() as $method) {
            if ($method->name->toString() === '__construct') {
                return $method;
            }
        }

        return null;
    }

    private function shouldSuggestInterface(string $typeName): bool
    {
        if (str_ends_with($typeName, 'Interface')) {
            return false;
        }

        if (str_starts_with($typeName, 'Illuminate\\')) {
            return false;
        }

        return str_starts_with($typeName, 'App\\');
    }

    private function isWatchedPath(string $relativePath): bool
    {
        $normalized = str_replace('\\', '/', $relativePath);

        foreach (self::WATCHED_SEGMENTS as $segment) {
            if (str_contains($normalized, $segment.'/')) {
                return true;
            }
        }

        return false;
    }
}
