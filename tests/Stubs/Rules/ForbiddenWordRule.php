<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Tests\Stubs\Rules;

use LaravelGuard\Guard\Rules\AbstractRule;
use LaravelGuard\Guard\Support\ScanFile;
use LaravelGuard\Guard\Violations\Severity;
use LaravelGuard\Guard\Violations\Violation;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;

final class ForbiddenWordRule extends AbstractRule
{
    private const string FORBIDDEN = 'guard-forbidden-marker';

    public function evaluate(ScanFile $file): array
    {
        $contents = @file_get_contents($file->absolutePath);

        if ($contents === false || ! str_contains($contents, self::FORBIDDEN)) {
            return [];
        }

        $finder = new NodeFinder;
        $line = 0;

        foreach ($finder->findInstanceOf($file->statements, Class_::class) as $class) {
            if ($class instanceof Class_) {
                $line = $class->getStartLine() ?? 0;

                break;
            }
        }

        return [
            new Violation(
                severity: Severity::Warning,
                file: $file->relativePath,
                line: $line,
                message: 'Forbidden marker word found in class file.',
                suggestion: 'Remove the forbidden marker from production code.',
                ruleId: $this->id(),
            ),
        ];
    }
}
