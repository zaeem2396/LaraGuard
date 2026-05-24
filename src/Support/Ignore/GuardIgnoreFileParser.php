<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support\Ignore;

final class GuardIgnoreFileParser
{
    /**
     * @return list<IgnorePattern>
     */
    public static function parseFile(string $absolutePath): array
    {
        if (! is_file($absolutePath)) {
            return [];
        }

        $contents = @file_get_contents($absolutePath);

        if ($contents === false) {
            return [];
        }

        return self::parseContents($contents);
    }

    /**
     * @return list<IgnorePattern>
     */
    public static function parseContents(string $contents): array
    {
        $patterns = [];

        foreach (preg_split('/\R/', $contents) ?: [] as $line) {
            $pattern = self::parseLine($line);

            if ($pattern !== null) {
                $patterns[] = $pattern;
            }
        }

        return $patterns;
    }

    private static function parseLine(string $line): ?IgnorePattern
    {
        $trimmed = trim($line);

        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            return null;
        }

        $negated = str_starts_with($trimmed, '!');
        $pattern = $negated ? ltrim(substr($trimmed, 1)) : $trimmed;

        if ($pattern === '') {
            return null;
        }

        return $negated
            ? IgnorePattern::exclude($pattern)
            : IgnorePattern::include($pattern);
    }
}
