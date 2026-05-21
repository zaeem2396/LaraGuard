<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support;

use PhpParser\Node\Stmt\Class_;

/**
 * Computes inclusive line span for a class AST node.
 */
final class ClassLineSpan
{
    public static function inclusiveLineCount(Class_ $class): int
    {
        $start = $class->getStartLine();
        $end = $class->getEndLine();

        if ($start <= 0 || $end <= 0 || $end < $start) {
            return 0;
        }

        return $end - $start + 1;
    }
}
