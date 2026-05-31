<?php

declare(strict_types=1);

namespace LaravelGuard\Guard;

use Illuminate\Contracts\Foundation\Application;
use LaravelGuard\Guard\Contracts\RuleContract;
use LaravelGuard\Guard\Support\GuardAnalysisEngine;
use LaravelGuard\Guard\Support\RuleExtensionRegistry;
use LaravelGuard\Guard\Support\RuleRegistry;

final class GuardManager
{
    /** @var list<callable(Application): void> */
    private array $bootCallbacks = [];

    public function __construct(
        private Application $app,
    ) {}

    /**
     * Register a rule class to run alongside configured rules.
     *
     * @param  class-string<RuleContract>  $ruleClass
     */
    public function extend(string $ruleClass): void
    {
        $this->app->make(RuleExtensionRegistry::class)->add($ruleClass);
        $this->forgetResolvedGuardServices();
    }

    /**
     * Register a callback invoked after the application has booted.
     *
     * Use in package service providers to register rules once the app is ready.
     */
    public function booting(callable $callback): void
    {
        $this->bootCallbacks[] = $callback;
    }

    public function invokeBootCallbacks(): void
    {
        foreach ($this->bootCallbacks as $callback) {
            $callback($this->app);
        }
    }

    private function forgetResolvedGuardServices(): void
    {
        if ($this->app->resolved(RuleRegistry::class)) {
            $this->app->forgetInstance(RuleRegistry::class);
        }

        if ($this->app->resolved(GuardAnalysisEngine::class)) {
            $this->app->forgetInstance(GuardAnalysisEngine::class);
        }

        if ($this->app->resolved(Console\GuardCommand::class)) {
            $this->app->forgetInstance(Console\GuardCommand::class);
        }
    }
}
