<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support\Ignore;

final class GlobPathMatcher
{
    public static function matches(string $pattern, string $relativePath): bool
    {
        $pattern = self::normalize($pattern);
        $path = self::normalize($relativePath);

        if ($pattern === '') {
            return false;
        }

        $anchored = str_starts_with($pattern, '/');
        if ($anchored) {
            $pattern = ltrim($pattern, '/');
            $regex = self::globToRegex($pattern, anchored: true);

            return (bool) preg_match($regex, $path);
        }

        if (str_contains($pattern, '*') || str_contains($pattern, '?')) {
            $regex = self::globToRegex($pattern, anchored: false);

            return (bool) preg_match($regex, $path);
        }

        if (str_ends_with($pattern, '/')) {
            $directory = rtrim($pattern, '/');

            return $path === $directory
                || str_starts_with($path, $directory.'/');
        }

        return $path === $pattern
            || str_starts_with($path, $pattern.'/')
            || str_contains($path, '/'.$pattern.'/')
            || str_ends_with($path, '/'.$pattern);
    }

    private static function normalize(string $value): string
    {
        return str_replace('\\', '/', trim($value));
    }

    private static function globToRegex(string $pattern, bool $anchored): string
    {
        $regex = '';
        $length = strlen($pattern);
        $index = 0;

        while ($index < $length) {
            if ($index + 1 < $length && $pattern[$index] === '*' && $pattern[$index + 1] === '*') {
                $regex .= '.*';
                $index += 2;

                if ($index < $length && $pattern[$index] === '/') {
                    $index++;
                }

                continue;
            }

            $char = $pattern[$index];
            $regex .= match ($char) {
                '*' => '[^/]*',
                '?' => '[^/]',
                default => preg_quote($char, '#'),
            };
            $index++;
        }

        if ($anchored) {
            return '#^'.$regex.'$#';
        }

        return '#(^|/)'.$regex.'($|/)#';
    }
}
