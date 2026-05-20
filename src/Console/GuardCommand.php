<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Console;

use Illuminate\Console\Command;
use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Support\Console\GuardConsoleRenderer;
use LaravelGuard\Guard\Support\GuardAnalysisEngine;
use LaravelGuard\Guard\Violations\Violation;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

final class GuardCommand extends Command
{
    protected $signature = 'guard
        {--fail-on-error : Fail with a non-zero exit code when configured severity thresholds are exceeded}
        {--format=txt : Output format (txt or json)}';

    protected $description = 'Architectural guard rails for your Laravel application.';

    public function __construct(
        private readonly GuardAnalysisEngine $engine,
        private readonly GuardConfig $config,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $violations = $this->engine->run();
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $io = new SymfonyStyle($this->input, $this->output);
        $renderer = new GuardConsoleRenderer($this->config, $io);

        $formatOption = $this->option('format');
        $format = is_string($formatOption) ? $formatOption : 'txt';

        if ($format === 'json') {
            $payload = $renderer->toJsonPayload($violations);
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

            return $this->resolveExitCode($violations);
        }

        if ($format !== 'txt') {
            $this->components->error('Unsupported format. Allowed values: txt, json.');

            return self::FAILURE;
        }

        $renderer->renderHuman($violations);

        return $this->resolveExitCode($violations);
    }

    /**
     * @param  list<Violation>  $violations
     */
    private function resolveExitCode(array $violations): int
    {
        if (! (bool) $this->option('fail-on-error')) {
            return self::SUCCESS;
        }

        $failLevel = $this->config->failOn;

        foreach ($violations as $violation) {
            if (! $violation->severity->isAtLeast($this->config->reportFrom)) {
                continue;
            }

            if ($violation->severity->isAtLeast($failLevel)) {
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
