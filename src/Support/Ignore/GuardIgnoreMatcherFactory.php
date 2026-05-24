<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support\Ignore;

use LaravelGuard\Guard\Config\GuardConfig;

final class GuardIgnoreMatcherFactory
{
    /**
     * @return list<IgnorePattern>
     */
    public static function buildPatterns(string $basePath, GuardConfig $config): array
    {
        $patterns = self::patternsFromConfigPrefixes($config->ignorePrefixes);

        $guardIgnorePath = rtrim($basePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'.guardignore';
        $filePatterns = GuardIgnoreFileParser::parseFile($guardIgnorePath);

        return array_merge($patterns, $filePatterns);
    }

    public static function create(string $basePath, GuardConfig $config): GuardIgnoreMatcher
    {
        return new GuardIgnoreMatcher(self::buildPatterns($basePath, $config));
    }

    /**
     * @param  list<string>  $prefixes
     * @return list<IgnorePattern>
     */
    private static function patternsFromConfigPrefixes(array $prefixes): array
    {
        $patterns = [];

        foreach ($prefixes as $prefix) {
            $normalized = str_replace('\\', '/', trim($prefix));

            if ($normalized === '') {
                continue;
            }

            $patterns[] = IgnorePattern::include($normalized);
            $patterns[] = IgnorePattern::include($normalized.'/**');
        }

        return $patterns;
    }
}
