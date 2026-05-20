<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Scanners;

use Illuminate\Contracts\Foundation\Application;
use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Contracts\ScanCacheContract;
use LaravelGuard\Guard\Contracts\ScannerContract;
use LaravelGuard\Guard\Support\ScanFile;
use PhpParser\Error as PhpParserError;
use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;

final readonly class PhpParserScanner implements ScannerContract
{
    public function __construct(
        private Application $application,
        private GuardConfig $config,
        private ScanCacheContract $cache,
    ) {}

    public function scan(): array
    {
        $parser = (new ParserFactory)->createForHostVersion();
        $files = [];

        foreach ($this->config->scanRoots as $root) {
            $absoluteRoot = $this->application->basePath($root);

            if (! is_dir($absoluteRoot)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absoluteRoot, \FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $item) {
                if (! $item instanceof \SplFileInfo || ! $item->isFile()) {
                    continue;
                }

                if ($item->getExtension() !== 'php') {
                    continue;
                }

                $path = $item->getPathname();

                if ($this->shouldIgnore($path)) {
                    continue;
                }

                $relative = $this->relativeToBase($path);

                $cacheKey = $this->cacheKeyFor($path);

                /** @var array{ok: true, statements: list<Stmt>}|array{ok: false} $payload */
                $payload = $this->cache->remember($cacheKey, function () use ($parser, $path): array {
                    $contents = @file_get_contents($path);

                    if ($contents === false) {
                        return ['ok' => false];
                    }

                    try {
                        $statements = $parser->parse($contents);

                        if ($statements === null) {
                            return ['ok' => false];
                        }

                        return ['ok' => true, 'statements' => $statements];
                    } catch (PhpParserError) {
                        return ['ok' => false];
                    }
                });

                if (! $payload['ok']) {
                    continue;
                }

                $files[] = new ScanFile(
                    absolutePath: $path,
                    relativePath: $relative,
                    statements: $payload['statements'],
                );
            }
        }

        return $files;
    }

    private function shouldIgnore(string $absolutePath): bool
    {
        $normalizedBase = rtrim($this->application->basePath(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $relative = ltrim(str_replace($normalizedBase, '', $absolutePath), DIRECTORY_SEPARATOR);

        foreach ($this->config->ignorePrefixes as $prefix) {
            if (str_starts_with($relative, rtrim($prefix, '/').'/') || $relative === rtrim($prefix, '/')) {
                return true;
            }
        }

        return false;
    }

    private function relativeToBase(string $absolutePath): string
    {
        $normalizedBase = rtrim($this->application->basePath(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        return ltrim(str_replace($normalizedBase, '', $absolutePath), DIRECTORY_SEPARATOR);
    }

    private function cacheKeyFor(string $absolutePath): string
    {
        $mtime = @filemtime($absolutePath) ?: 0;

        return 'guard.parse.'.hash('xxh3', $absolutePath.'|'.$mtime);
    }
}
