<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Rules;

use LaravelGuard\Guard\Contracts\RuleContract;
use LaravelGuard\Guard\Support\ImportAliasMap;
use LaravelGuard\Guard\Support\ScanFile;
use LaravelGuard\Guard\Violations\Severity;
use LaravelGuard\Guard\Violations\Violation;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\NodeFinder;

final readonly class NoDbInControllerRule implements RuleContract
{
    public function id(): string
    {
        return 'no-db-in-controller';
    }

    public function evaluate(ScanFile $file): array
    {
        if (! $this->isLikelyControllerPath($file->relativePath)) {
            return [];
        }

        $importMap = ImportAliasMap::fromStatements($file->statements);
        $finder = new NodeFinder;
        $violations = [];

        foreach ($finder->findInstanceOf($file->statements, StaticCall::class) as $call) {
            if (! $call instanceof StaticCall) {
                continue;
            }

            $reason = $this->describeStaticCallViolation($call, $importMap);

            if ($reason === null) {
                continue;
            }

            $violations[] = new Violation(
                severity: Severity::Error,
                file: $file->relativePath,
                line: $call->getStartLine(),
                message: $reason,
                suggestion: 'Move persistence behind Actions, Services, or Repositories and inject abstractions.',
                ruleId: $this->id(),
            );
        }

        return $violations;
    }

    /**
     * @param  array<string, string>  $importMap
     */
    private function describeStaticCallViolation(StaticCall $call, array $importMap): ?string
    {
        if (! $call->class instanceof Name) {
            return null;
        }

        $resolved = ImportAliasMap::resolveName($call->class, $importMap);

        if ($this->isDbFacade($resolved)) {
            return 'Database facades should not be used directly inside HTTP controllers.';
        }

        if ($this->isLikelyEloquentModelReference($resolved) && $this->isEloquentQueryMethod($this->methodName($call))) {
            return 'Eloquent models should not be queried directly inside HTTP controllers.';
        }

        return null;
    }

    private function isDbFacade(string $name): bool
    {
        $normalized = ltrim($name, '\\');

        return $normalized === 'DB'
            || $normalized === 'Illuminate\\Support\\Facades\\DB';
    }

    private function isLikelyEloquentModelReference(string $name): bool
    {
        $normalized = ltrim($name, '\\');

        if (str_contains($normalized, '\\Models\\')) {
            return true;
        }

        return str_ends_with($normalized, 'Model');
    }

    private function isEloquentQueryMethod(string $method): bool
    {
        return in_array($method, [
            'query', 'find', 'findOrFail', 'where', 'create', 'updateOrCreate',
            'first', 'firstOrFail', 'all', 'get', 'pluck', 'count', 'exists',
        ], true);
    }

    private function methodName(StaticCall $call): string
    {
        if ($call->name instanceof Identifier) {
            return $call->name->toString();
        }

        return '';
    }

    private function isLikelyControllerPath(string $relativePath): bool
    {
        $normalized = str_replace('\\', '/', $relativePath);

        return str_contains($normalized, 'Http/Controllers');
    }
}
