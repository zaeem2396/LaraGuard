<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Rules;

use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Contracts\RuleContract;
use LaravelGuard\Guard\Support\Layers\LayerDependencyPolicy;
use LaravelGuard\Guard\Support\Layers\LayerResolver;
use LaravelGuard\Guard\Support\Layers\ReferencedTypeCollector;
use LaravelGuard\Guard\Support\ScanFile;
use LaravelGuard\Guard\Violations\Severity;
use LaravelGuard\Guard\Violations\Violation;

final readonly class LayerViolationRule implements RuleContract
{
    public function __construct(
        private GuardConfig $config,
    ) {}

    public function id(): string
    {
        return 'layer-violation';
    }

    public function evaluate(ScanFile $file): array
    {
        $layerConfig = $this->config->layerArchitecture;

        if ($layerConfig === null) {
            return [];
        }

        $resolver = new LayerResolver($layerConfig);
        $policy = new LayerDependencyPolicy($layerConfig, $resolver);
        $sourceLayer = $resolver->layerForFile($file->statements, $file->relativePath);

        if ($sourceLayer === null) {
            return [];
        }

        $violations = [];

        foreach (ReferencedTypeCollector::collect($file->statements) as $reference) {
            $targetFqcn = $reference['fqcn'];

            if (! str_starts_with($targetFqcn, 'App\\')) {
                continue;
            }

            if ($policy->allows($sourceLayer, $targetFqcn)) {
                continue;
            }

            $targetLayer = $resolver->layerForFqcn($targetFqcn);

            $violations[] = new Violation(
                severity: Severity::Warning,
                file: $file->relativePath,
                line: $reference['line'],
                message: sprintf(
                    'Layer "%s" must not depend on "%s"%s.',
                    $sourceLayer,
                    $targetLayer ?? $targetFqcn,
                    $targetLayer !== null ? ' ('.$targetFqcn.')' : '',
                ),
                suggestion: $this->suggestionFor($sourceLayer, $targetLayer),
                ruleId: $this->id(),
            );
        }

        return $violations;
    }

    private function suggestionFor(string $fromLayer, ?string $toLayer): string
    {
        $allowed = $this->config->layerArchitecture?->allowedDependencies[$fromLayer] ?? [];

        if ($allowed === []) {
            return 'Route dependencies through the next inner layer or add an explicit exception in layer_violation config.';
        }

        return sprintf(
            'Depend on %s instead, or add a layer_violation exception if this coupling is intentional.',
            implode(' or ', $allowed),
        );
    }
}
