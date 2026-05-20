<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support;

use LaravelGuard\Guard\Contracts\RuleContract;

final class RuleRegistry
{
    /** @var list<RuleContract> */
    private array $rules = [];

    public function register(RuleContract $rule): void
    {
        $this->rules[] = $rule;
    }

    /**
     * @param  list<RuleContract>  $rules
     */
    public function registerMany(array $rules): void
    {
        foreach ($rules as $rule) {
            $this->register($rule);
        }
    }

    /**
     * @return list<RuleContract>
     */
    public function all(): array
    {
        return $this->rules;
    }
}
