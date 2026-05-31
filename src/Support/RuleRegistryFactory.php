<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support;

use Illuminate\Contracts\Foundation\Application;
use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Contracts\RuleContract;

final class RuleRegistryFactory
{
    public static function create(Application $app): RuleRegistry
    {
        $registry = new RuleRegistry;
        $config = $app->make(GuardConfig::class);
        $extensions = $app->make(RuleExtensionRegistry::class);

        foreach (self::mergedRuleClasses($config, $extensions) as $ruleClass) {
            $rule = $app->make($ruleClass);

            if (! $rule instanceof RuleContract) {
                continue;
            }

            if (! $config->isRuleEnabled($rule->id())) {
                continue;
            }

            $registry->register($rule);
        }

        return $registry;
    }

    /**
     * @return list<class-string<RuleContract>>
     */
    private static function mergedRuleClasses(GuardConfig $config, RuleExtensionRegistry $extensions): array
    {
        /** @var list<class-string<RuleContract>> $merged */
        $merged = array_values(array_unique([
            ...$config->rules,
            ...$extensions->all(),
        ]));

        return $merged;
    }
}
