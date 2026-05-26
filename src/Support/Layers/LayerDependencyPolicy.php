<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support\Layers;

use LaravelGuard\Guard\Config\LayerArchitectureConfig;

final readonly class LayerDependencyPolicy
{
    public function __construct(
        private LayerArchitectureConfig $config,
        private LayerResolver $resolver,
    ) {}

    public function allows(string $fromLayer, string $targetFqcn): bool
    {
        $normalized = ltrim($targetFqcn, '\\');

        if ($this->matchesAllowedNamespacePrefix($normalized)) {
            return true;
        }

        if ($this->matchesConfiguredException($fromLayer, $normalized)) {
            return true;
        }

        $toLayer = $this->resolver->layerForFqcn($normalized);

        if ($toLayer === null) {
            return true;
        }

        $allowedTargets = $this->config->allowedDependencies[$fromLayer] ?? [];

        return in_array($toLayer, $allowedTargets, true);
    }

    private function matchesAllowedNamespacePrefix(string $fqcn): bool
    {
        foreach ($this->config->allowedNamespacePrefixes as $prefix) {
            if ($fqcn === rtrim($prefix, '\\') || str_starts_with($fqcn, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function matchesConfiguredException(string $fromLayer, string $targetFqcn): bool
    {
        foreach ($this->config->exceptions as $exception) {
            if (($exception['from'] ?? '') !== $fromLayer) {
                continue;
            }

            if (isset($exception['to_layer'])) {
                $toLayer = $this->resolver->layerForFqcn($targetFqcn);

                if ($toLayer === $exception['to_layer']) {
                    return true;
                }
            }

            if (isset($exception['to_prefix'])) {
                $prefix = ltrim($exception['to_prefix'], '\\');

                if ($targetFqcn === $prefix || str_starts_with($targetFqcn, $prefix.'\\')) {
                    return true;
                }
            }
        }

        return false;
    }
}
