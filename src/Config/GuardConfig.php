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
     * @param  array<string, RuleOption>  $ruleOptions
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
        public array $ruleOptions = [],
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
            scanRoots: self::resolveScanRoots($config['paths'] ?? ['app']),
            noDbControllerExcludePrefixes: self::stringListFrom($noDbConfig['exclude_path_prefixes'] ?? []),
            providerBindingScanPaths: self::normalizedScanRoots($bindingConfig['scan_paths'] ?? ['app/Providers']),
            missingInterfaceBindingStrict: (bool) ($missingInterfaceConfig['strict'] ?? false),
            ruleOptions: self::parseRuleOptions($config['rule_options'] ?? []),
        );
    }

    public function isRuleEnabled(string $ruleId): bool
    {
        if (! isset($this->ruleOptions[$ruleId])) {
            return true;
        }

        return $this->ruleOptions[$ruleId]->enabled;
    }

    public function ruleOption(string $ruleId): ?RuleOption
    {
        return $this->ruleOptions[$ruleId] ?? null;
    }

    /**
     * @return array<string, RuleOption>
     */
    private static function parseRuleOptions(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $options = [];

        foreach ($value as $ruleId => $raw) {
            if (! is_string($ruleId) || ! is_array($raw)) {
                continue;
            }

            $enabled = array_key_exists('enabled', $raw)
                ? (bool) $raw['enabled']
                : true;

            $severity = null;
            $severityRaw = $raw['severity'] ?? null;

            if (is_string($severityRaw)) {
                $severity = Severity::tryFrom($severityRaw);
            }

            $options[$ruleId] = new RuleOption(
                enabled: $enabled,
                severity: $severity,
            );
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    private static function resolveScanRoots(mixed $configured): array
    {
        if (is_array($configured) && $configured === []) {
            throw InvalidGuardConfigurationException::emptyScanPaths();
        }

        $roots = self::stringListFrom(is_array($configured) ? $configured : ['app']);

        if ($roots === []) {
            throw InvalidGuardConfigurationException::emptyScanPaths();
        }

        return $roots;
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
