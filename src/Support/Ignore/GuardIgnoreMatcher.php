<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support\Ignore;

final class GuardIgnoreMatcher
{
    /** @var list<IgnorePattern> */
    private array $patterns;

    /**
     * @param  list<IgnorePattern>  $patterns
     */
    public function __construct(array $patterns)
    {
        $this->patterns = $patterns;
    }

    public function shouldIgnore(string $relativePath): bool
    {
        $ignored = false;

        foreach ($this->patterns as $pattern) {
            if (! GlobPathMatcher::matches($pattern->pattern, $relativePath)) {
                continue;
            }

            $ignored = ! $pattern->negated;
        }

        return $ignored;
    }

    /**
     * @return list<IgnorePattern>
     */
    public function patterns(): array
    {
        return $this->patterns;
    }
}
