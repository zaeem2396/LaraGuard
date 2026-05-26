<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Config;

final readonly class LayerArchitectureConfig
{
    /**
     * @param  list<string>  $order
     * @param  array<string, list<string>>  $namespaces
     * @param  array<string, list<string>>  $allowedDependencies
     * @param  list<string>  $allowedNamespacePrefixes
     * @param  list<array{from: string, to_layer?: string, to_prefix?: string}>  $exceptions
     */
    public function __construct(
        public array $order,
        public array $namespaces,
        public array $allowedDependencies,
        public array $allowedNamespacePrefixes,
        public array $exceptions,
    ) {}

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromGuardConfig(array $config): ?self
    {
        $layersRaw = $config['layers'] ?? [];
        $layersConfig = is_array($layersRaw) ? $layersRaw : [];

        $order = self::stringListFrom($layersConfig['order'] ?? []);

        if ($order === []) {
            return null;
        }

        $namespaces = self::parseNamespaces($layersConfig['namespaces'] ?? [], $order);
        $violationRaw = $config['layer_violation'] ?? [];
        $violationConfig = is_array($violationRaw) ? $violationRaw : [];

        $allowedDependencies = self::parseAllowedDependencies(
            $violationConfig['allowed'] ?? [],
            $order,
        );

        $allowedNamespacePrefixes = self::normalizedPrefixes(
            self::stringListFrom($violationConfig['allowed_namespace_prefixes'] ?? []),
        );

        if ($allowedNamespacePrefixes === []) {
            $allowedNamespacePrefixes = self::defaultAllowedNamespacePrefixes();
        }

        $exceptions = self::parseExceptions($violationConfig['exceptions'] ?? []);

        return new self(
            order: $order,
            namespaces: $namespaces,
            allowedDependencies: $allowedDependencies,
            allowedNamespacePrefixes: $allowedNamespacePrefixes,
            exceptions: $exceptions,
        );
    }

    /**
     * @param  list<string>  $order
     * @return array<string, list<string>>
     */
    private static function parseNamespaces(mixed $value, array $order): array
    {
        if (! is_array($value)) {
            return self::defaultNamespaces($order);
        }

        $namespaces = [];

        foreach ($order as $layer) {
            $prefixes = $value[$layer] ?? null;

            if (! is_array($prefixes)) {
                continue;
            }

            $normalized = [];

            foreach ($prefixes as $prefix) {
                if (is_string($prefix) && $prefix !== '') {
                    $normalized[] = ltrim($prefix, '\\');
                }
            }

            if ($normalized !== []) {
                $namespaces[$layer] = $normalized;
            }
        }

        return $namespaces !== [] ? $namespaces : self::defaultNamespaces($order);
    }

    /**
     * @param  list<string>  $order
     * @return array<string, list<string>>
     */
    private static function defaultNamespaces(array $order): array
    {
        $defaults = [
            'Controller' => ['App\\Http\\Controllers'],
            'Service' => ['App\\Services'],
            'Repository' => ['App\\Repositories'],
            'Model' => ['App\\Models'],
        ];

        $namespaces = [];

        foreach ($order as $layer) {
            if (isset($defaults[$layer])) {
                $namespaces[$layer] = $defaults[$layer];
            }
        }

        return $namespaces;
    }

    /**
     * @param  list<string>  $order
     * @return array<string, list<string>>
     */
    private static function parseAllowedDependencies(mixed $value, array $order): array
    {
        if (! is_array($value) || $value === []) {
            return self::defaultAllowedDependencies($order);
        }

        $allowed = [];

        foreach ($order as $layer) {
            $targets = $value[$layer] ?? null;

            if (! is_array($targets)) {
                continue;
            }

            $normalized = [];

            foreach ($targets as $target) {
                if (is_string($target) && $target !== '' && in_array($target, $order, true)) {
                    $normalized[] = $target;
                }
            }

            if ($normalized !== []) {
                $allowed[$layer] = array_values(array_unique($normalized));
            }
        }

        return $allowed !== [] ? $allowed : self::defaultAllowedDependencies($order);
    }

    /**
     * @param  list<string>  $order
     * @return array<string, list<string>>
     */
    private static function defaultAllowedDependencies(array $order): array
    {
        $defaults = [
            'Controller' => ['Service'],
            'Service' => ['Repository', 'Service'],
            'Repository' => ['Model', 'Repository'],
            'Model' => ['Model'],
        ];

        $allowed = [];

        foreach ($order as $layer) {
            if (isset($defaults[$layer])) {
                $targets = array_values(array_filter(
                    $defaults[$layer],
                    static fn (string $target): bool => in_array($target, $order, true),
                ));

                if ($targets !== []) {
                    $allowed[$layer] = $targets;
                }
            }
        }

        return $allowed;
    }

    /**
     * @return list<string>
     */
    private static function defaultAllowedNamespacePrefixes(): array
    {
        return self::normalizedPrefixes([
            'Illuminate\\',
            'Laravel\\',
            'App\\Http\\Requests\\',
            'App\\Http\\Resources\\',
            'App\\Http\\Middleware\\',
            'App\\Enums\\',
            'App\\Contracts\\',
            'App\\Data\\',
            'App\\DTO\\',
            'App\\Dto\\',
            'App\\ViewModels\\',
            'App\\Events\\',
            'App\\Listeners\\',
            'App\\Jobs\\',
            'App\\Mail\\',
            'App\\Notifications\\',
            'App\\Policies\\',
            'App\\Rules\\',
            'App\\Exceptions\\',
            'App\\Support\\',
            'App\\ValueObjects\\',
            'Symfony\\',
            'Psr\\',
            'PhpParser\\',
            'Carbon\\',
        ]);
    }

    /**
     * @return list<array{from: string, to_layer?: string, to_prefix?: string}>
     */
    private static function parseExceptions(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $exceptions = [];

        foreach ($value as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $from = $entry['from'] ?? null;

            if (! is_string($from) || $from === '') {
                continue;
            }

            $parsed = ['from' => $from];

            if (isset($entry['to_layer']) && is_string($entry['to_layer']) && $entry['to_layer'] !== '') {
                $parsed['to_layer'] = $entry['to_layer'];
            }

            if (isset($entry['to_prefix']) && is_string($entry['to_prefix']) && $entry['to_prefix'] !== '') {
                $parsed['to_prefix'] = ltrim($entry['to_prefix'], '\\');
            }

            if (isset($parsed['to_layer']) || isset($parsed['to_prefix'])) {
                $exceptions[] = $parsed;
            }
        }

        return $exceptions;
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
     * @param  list<string>  $prefixes
     * @return list<string>
     */
    private static function normalizedPrefixes(array $prefixes): array
    {
        $normalized = [];

        foreach ($prefixes as $prefix) {
            $trimmed = ltrim($prefix, '\\');

            if ($trimmed === '') {
                continue;
            }

            $normalized[] = str_ends_with($trimmed, '\\')
                ? $trimmed
                : $trimmed.'\\';
        }

        return array_values(array_unique($normalized));
    }
}
