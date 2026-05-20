<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Rules;

use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Contracts\RuleContract;
use LaravelGuard\Guard\Support\ScanFile;
use LaravelGuard\Guard\Violations\Severity;
use LaravelGuard\Guard\Violations\Violation;
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
        $maxRaw = $fat['max_method_count'] ?? 20;
        $maxMethods = is_numeric($maxRaw) ? (int) $maxRaw : 20;

        $finder = new NodeFinder;
        $methods = array_values($finder->findInstanceOf($file->statements, ClassMethod::class));

        if ($maxMethods <= 0 || $methods === [] || count($methods) <= $maxMethods) {
            return [];
        }

        $line = $this->guessClassLine($methods);

        return [
            new Violation(
                severity: Severity::Info,
                file: $file->relativePath,
                line: $line,
                message: sprintf('Class defines %d methods, exceeding the threshold of %d.', count($methods), $maxMethods),
                suggestion: 'Consider extracting cohesive behavior into dedicated classes.',
                ruleId: $this->id(),
            ),
        ];
    }

    /**
     * @param  list<ClassMethod>  $methods
     */
    private function guessClassLine(array $methods): int
    {
        foreach ($methods as $method) {
            $line = $method->getStartLine() ?? null;

            if ($line !== null) {
                return $line;
            }
        }

        return 0;
    }
}
