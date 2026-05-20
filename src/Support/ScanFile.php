<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support;

use PhpParser\Node\Stmt;

/**
 * @immutable
 */
final readonly class ScanFile
{
    /**
     * @param  list<Stmt>  $statements
     */
    public function __construct(
        public string $absolutePath,
        public string $relativePath,
        public array $statements,
    ) {}
}
