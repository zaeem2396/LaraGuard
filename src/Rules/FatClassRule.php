<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Rules;

use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Contracts\RuleContract;
use LaravelGuard\Guard\Support\ScanFile;
use LaravelGuard\Guard\Violations\Severity;
use LaravelGuard\Guard\Violations\Violation;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;

final readonly class FatClassRule implements RuleContract
{
    public function __construct(
        private GuardConfig $config,
    ) {}

    public function id(): string
    {
        return 'fat-class';
    }

    public function evaluate(ScanFile $file): array
    {
        $fatRaw = $this->config->thresholds['fat_class'] ?? [];
        $fat = is_array($fatRaw) ? $fatRaw : [];
        $maxMethods = $this->intThreshold($fat['max_method_count'] ?? 20, 20);
        $maxPublicMethods = $this->intThreshold($fat['max_public_method_count'] ?? 12, 12);

        if ($maxMethods <= 0 && $maxPublicMethods <= 0) {
            return [];
        }

        $finder = new NodeFinder;
        $violations = [];

        foreach ($finder->findInstanceOf($file->statements, Class_::class) as $class) {
            if (! $class instanceof Class_ || $class->isAnonymous()) {
                continue;
            }

            $methods = array_values(array_filter(
                $class->getMethods(),
                static fn (ClassMethod $method): bool => ! $method->isAbstract(),
            ));

            $publicMethods = array_values(array_filter(
                $methods,
                static fn (ClassMethod $method): bool => $method->isPublic(),
            ));

            $className = $class->name !== null ? $class->name->toString() : 'anonymous class';
            $line = $class->getStartLine();

            if ($maxMethods > 0 && count($methods) > $maxMethods) {
                $violations[] = $this->violation(
                    file: $file->relativePath,
                    line: $line,
                    message: sprintf(
                        'Class "%s" defines %d methods, exceeding the threshold of %d.',
                        $className,
                        count($methods),
                        $maxMethods,
                    ),
                );
            }

            if ($maxPublicMethods > 0 && count($publicMethods) > $maxPublicMethods) {
                $violations[] = $this->violation(
                    file: $file->relativePath,
                    line: $line,
                    message: sprintf(
                        'Class "%s" defines %d public methods, exceeding the threshold of %d.',
                        $className,
                        count($publicMethods),
                        $maxPublicMethods,
                    ),
                );
            }
        }

        return $violations;
    }

    private function intThreshold(mixed $value, int $default): int
    {
        return is_numeric($value) ? (int) $value : $default;
    }

    private function violation(string $file, int $line, string $message): Violation
    {
        return new Violation(
            severity: Severity::Info,
            file: $file,
            line: $line,
            message: $message,
            suggestion: 'Consider extracting cohesive behavior into dedicated classes.',
            ruleId: $this->id(),
        );
    }
}
