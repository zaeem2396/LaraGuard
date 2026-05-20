<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Rules;

use LaravelGuard\Guard\Contracts\RuleContract;
use LaravelGuard\Guard\Support\ScanFile;
use LaravelGuard\Guard\Violations\Severity;
use LaravelGuard\Guard\Violations\Violation;
use PhpParser\Node\Expr\StaticCall;
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

        $finder = new NodeFinder;

        foreach ($finder->findInstanceOf($file->statements, StaticCall::class) as $call) {
            if (! $call instanceof StaticCall || ! $call->class instanceof Name) {
                continue;
            }

            $fqcn = $call->class->toString();
            $isDbFacade = $fqcn === 'DB'
                || $fqcn === 'Illuminate\\Support\\Facades\\DB';

            if (! $isDbFacade) {
                continue;
            }

            return [
                new Violation(
                    severity: Severity::Warning,
                    file: $file->relativePath,
                    line: $call->getStartLine(),
                    message: 'Database facades should not be used directly inside HTTP controllers.',
                    suggestion: 'Move queries behind Actions, Services, or Repositories and inject dependencies instead.',
                    ruleId: $this->id(),
                ),
            ];
        }

        return [];
    }

    private function isLikelyControllerPath(string $relativePath): bool
    {
        $normalized = str_replace('\\', '/', $relativePath);

        return str_contains($normalized, 'Http/Controllers');
    }
}
