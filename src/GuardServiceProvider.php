<?php

declare(strict_types=1);

namespace LaravelGuard\Guard;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Console\GuardCommand;
use LaravelGuard\Guard\Contracts\RuleContract;
use LaravelGuard\Guard\Contracts\ScanCacheContract;
use LaravelGuard\Guard\Contracts\ScannerContract;
use LaravelGuard\Guard\Scanners\PhpParserScanner;
use LaravelGuard\Guard\Scanners\ServiceProviderBindingScanner;
use LaravelGuard\Guard\Support\ContainerBindingMap;
use LaravelGuard\Guard\Support\GuardAnalysisEngine;
use LaravelGuard\Guard\Support\NullScanCache;
use LaravelGuard\Guard\Support\RuleRegistry;

final class GuardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/guard.php', 'guard');

        $this->app->singleton(GuardConfig::class, static function (Application $app): GuardConfig {
            /** @var Repository $configRepository */
            $configRepository = $app->make(Repository::class);

            /** @var array<string, mixed> $config */
            $config = $configRepository->get('guard', []);

            return GuardConfig::fromArray($config);
        });

        $this->app->singleton(ScanCacheContract::class, NullScanCache::class);
        $this->app->bind(ScannerContract::class, PhpParserScanner::class);

        $this->app->singleton(ContainerBindingMap::class, function (Application $app): ContainerBindingMap {
            return $app->make(ServiceProviderBindingScanner::class)->scan();
        });

        $this->app->singleton(ServiceProviderBindingScanner::class);

        $this->app->singleton(RuleRegistry::class, function (Application $app): RuleRegistry {
            $registry = new RuleRegistry;

            foreach ($app->make(GuardConfig::class)->rules as $ruleClass) {
                $rule = $app->make($ruleClass);
                if (! $rule instanceof RuleContract) {
                    continue;
                }

                if (! $app->make(GuardConfig::class)->isRuleEnabled($rule->id())) {
                    continue;
                }

                $registry->register($rule);
            }

            return $registry;
        });

        $this->app->singleton(GuardAnalysisEngine::class, function (Application $app): GuardAnalysisEngine {
            return new GuardAnalysisEngine(
                $app->make(ScannerContract::class),
                $app->make(RuleRegistry::class),
                $app->make(GuardConfig::class),
            );
        });

        $this->app->singleton(GuardCommand::class, function (Application $app): GuardCommand {
            return new GuardCommand(
                $app->make(GuardAnalysisEngine::class),
                $app->make(GuardConfig::class),
            );
        });
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            GuardCommand::class,
        ]);

        $this->publishes([
            __DIR__.'/../config/guard.php' => config_path('guard.php'),
        ], 'guard-config');
    }
}
