<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support\Console;

use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Violations\Severity;
use LaravelGuard\Guard\Violations\Violation;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Style\SymfonyStyle;

final readonly class GuardConsoleRenderer
{
    public function __construct(
        private GuardConfig $config,
        private SymfonyStyle $io,
    ) {}

    /**
     * @param  list<Violation>  $violations
     */
    public function renderHuman(array $violations, int $filesScanned): int
    {
        $filtered = $this->filter($violations);

        $errors = array_values(array_filter($filtered, static fn (Violation $v): bool => $v->severity === Severity::Error));
        $warnings = array_values(array_filter($filtered, static fn (Violation $v): bool => $v->severity === Severity::Warning));
        $infos = array_values(array_filter($filtered, static fn (Violation $v): bool => $v->severity === Severity::Info));

        $this->io->newLine();
        $this->io->title('Laravel Guard');

        $this->renderSection('Errors', $errors, 'red');
        $this->renderSection('Warnings', $warnings, 'yellow');
        $this->renderSection('Info', $infos, 'cyan');

        $this->renderSummary($errors, $warnings, $infos, $filesScanned);

        return count($errors);
    }

    /**
     * @param  list<Violation>  $violations
     * @return array{errors: list<array<string, mixed>>, warnings: list<array<string, mixed>>, info: list<array<string, mixed>>, summary: array<string, int>}
     */
    public function toJsonPayload(array $violations, int $filesScanned): array
    {
        $filtered = $this->filter($violations);

        $errors = array_values(array_filter($filtered, static fn (Violation $v): bool => $v->severity === Severity::Error));
        $warnings = array_values(array_filter($filtered, static fn (Violation $v): bool => $v->severity === Severity::Warning));
        $infos = array_values(array_filter($filtered, static fn (Violation $v): bool => $v->severity === Severity::Info));

        return [
            'errors' => array_map(static fn (Violation $v): array => $v->toArray(), $errors),
            'warnings' => array_map(static fn (Violation $v): array => $v->toArray(), $warnings),
            'info' => array_map(static fn (Violation $v): array => $v->toArray(), $infos),
            'summary' => [
                'errors' => count($errors),
                'warnings' => count($warnings),
                'info' => count($infos),
                'files_scanned' => $filesScanned,
            ],
        ];
    }

    /**
     * @param  list<Violation>  $violations
     * @return list<Violation>
     */
    private function filter(array $violations): array
    {
        return array_values(array_filter(
            $violations,
            fn (Violation $violation): bool => $violation->severity->isAtLeast($this->config->reportFrom),
        ));
    }

    /**
     * @param  list<Violation>  $rows
     */
    private function renderSection(string $label, array $rows, string $color): void
    {
        if ($rows === []) {
            $this->io->section(sprintf('<fg=%s>%s</>', $color, $label));
            $this->io->text('  <fg=gray>None</>');

            return;
        }

        $this->io->section(sprintf('<fg=%s>%s</>', $color, $label));

        $table = new Table($this->io);
        $table->setHeaders(['File', 'Line', 'Message']);
        $table->setStyle('compact');

        foreach ($rows as $violation) {
            $message = $violation->message;

            if ($violation->suggestion !== null) {
                $message .= sprintf("\n<fg=gray>%s</>", $violation->suggestion);
            }

            $table->addRow([$violation->file, (string) $violation->line, $message]);
        }

        $table->render();
    }

    /**
     * @param  list<Violation>  $errors
     * @param  list<Violation>  $warnings
     * @param  list<Violation>  $infos
     */
    private function renderSummary(array $errors, array $warnings, array $infos, int $filesScanned): void
    {
        $this->io->newLine();
        $this->io->writeln('  <fg=gray>────────────</>');
        $this->io->writeln(sprintf('  <fg=gray>Files</>   : %d scanned', $filesScanned));
        $this->io->writeln(sprintf('  <fg=red>Errors</>  : %d', count($errors)));
        $this->io->writeln(sprintf('  <fg=yellow>Warnings</>: %d', count($warnings)));
        $this->io->writeln(sprintf('  <fg=cyan>Info</>    : %d', count($infos)));
    }
}
