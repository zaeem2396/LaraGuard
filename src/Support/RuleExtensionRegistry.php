<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support;

use InvalidArgumentException;
use LaravelGuard\Guard\Contracts\RuleContract;

final class RuleExtensionRegistry
{
    /** @var list<class-string<RuleContract>> */
    private array $ruleClasses = [];

    /**
     * @param  class-string<RuleContract>  $ruleClass
     */
    public function add(string $ruleClass): void
    {
        if (! class_exists($ruleClass)) {
            throw new InvalidArgumentException(sprintf('Guard rule class [%s] does not exist.', $ruleClass));
        }

        if (! is_subclass_of($ruleClass, RuleContract::class)) {
            throw new InvalidArgumentException(sprintf(
                'Guard rule class [%s] must implement %s.',
                $ruleClass,
                RuleContract::class,
            ));
        }

        if (in_array($ruleClass, $this->ruleClasses, true)) {
            return;
        }

        $this->ruleClasses[] = $ruleClass;
    }

    /**
     * @return list<class-string<RuleContract>>
     */
    public function all(): array
    {
        return $this->ruleClasses;
    }
}
