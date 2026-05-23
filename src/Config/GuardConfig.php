<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Config;

use LaravelGuard\Guard\Contracts\RuleContract;
use LaravelGuard\Guard\Violations\Severity;

final class GuardConfig
{
    /**
     * @param  list<class-string<RuleContract>>  $rules
     * @param  list<string>  $ignorePrefixes
     * @param  array<string, mixed>  $thresholds
     * @param  list<string>  $scanRoots
     * @param  list<string>  $noDbControllerExcludePrefixes
     * @param  list<string>  $providerBindingScanPaths
     */
    public function __construct(
        public array $rules,
        public array $ignorePrefixes,
        public array $thresholds,
        public Severity $reportFrom,
        public Severity $failOn,
        public array $scanRoots,
        public array $noDbControllerExcludePrefixes = [],
        public array $providerBindingScanPaths = ['app/Providers'],
        public bool $missingInterfaceBindingStrict = false,
    ) {}

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config): self
    {
        $severityRaw = $config['severity'] ?? [];
        $severityConfig = is_array($severityRaw) ? $severityRaw : [];

        $reportFromValue = $severityConfig['report_from'] ?? 'info';
        $failOnValue = $severityConfig['fail_on'] ?? 'error';

        $reportFrom = Severity::tryFrom(is_string($reportFromValue) ? $reportFromValue : 'info') ?? Severity::Info;
        $failOn = Severity::tryFrom(is_string($failOnValue) ? $failOnValue : 'error') ?? Severity::Error;

        $thresholdsRaw = $config['thresholds'] ?? [];
        $thresholds = [];
        if (is_array($thresholdsRaw)) {
            /** @var array<string, mixed> $thresholds */
            $thresholds = $thresholdsRaw;
        }

        $noDbRaw = $config['no_db_in_controller'] ?? [];
        $noDbConfig = is_array($noDbRaw) ? $noDbRaw : [];

        $bindingRaw = $config['provider_bindings'] ?? [];
        $bindingConfig = is_array($bindingRaw) ? $bindingRaw : [];

        $missingInterfaceRaw = $config['missing_interface_binding'] ?? [];
        $missingInterfaceConfig = is_array($missingInterfaceRaw) ? $missingInterfaceRaw : [];

        return new self(
            rules: array_values(array_filter(
                (array) ($config['rules'] ?? []),
                static function (mixed $class): bool {
                    return is_string($class)
                        && class_exists($class)
                        && is_subclass_of($class, RuleContract::class);
                },
            )),
            ignorePrefixes: self::stringListFrom($config['ignore'] ?? []),
            thresholds: $thresholds,
            reportFrom: $reportFrom,
            failOn: $failOn,
            scanRoots: self::normalizedScanRoots($config['paths'] ?? ['app']),
            noDbControllerExcludePrefixes: self::stringListFrom($noDbConfig['exclude_path_prefixes'] ?? []),
            providerBindingScanPaths: self::normalizedScanRoots($bindingConfig['scan_paths'] ?? ['app/Providers']),
            missingInterfaceBindingStrict: (bool) ($missingInterfaceConfig['strict'] ?? false),
        );
    }

    /**
     * @return list<string>
     */
    private static function stringListFrom(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $strings = [];

        foreach ($value as $item) {
            if (is_string($item) && $item !== '') {
                $strings[] = $item;
            }
        }

        return $strings;
    }

    /**
     * @return list<string>
     */
    private static function normalizedScanRoots(mixed $value): array
    {
        $roots = self::stringListFrom(is_array($value) ? $value : ['app']);

        return $roots === [] ? ['app'] : $roots;
    }
}
