<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Tests;

use LaravelGuard\Guard\GuardServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->setBasePath($this->fixtureBasePath());
    }

    protected function getPackageProviders($app): array
    {
        return [
            GuardServiceProvider::class,
        ];
    }

    protected function fixtureBasePath(): string
    {
        return __DIR__.'/fixtures/violations';
    }
}
