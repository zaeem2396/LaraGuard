<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Support;

/**
 * In-memory map of container bindings discovered from service providers.
 *
 * @phpstan-type BindingMap array<string, string>
 */
final readonly class ContainerBindingMap
{
    /**
     * @param  BindingMap  $interfaceToConcrete  interface FQCN => concrete FQCN
     */
    public function __construct(
        private array $interfaceToConcrete = [],
    ) {}

    /**
     * @param  BindingMap  $interfaceToConcrete
     */
    public static function fromArray(array $interfaceToConcrete): self
    {
        $normalized = [];

        foreach ($interfaceToConcrete as $interface => $concrete) {
            if (! is_string($interface) || ! is_string($concrete)) {
                continue;
            }

            $interfaceKey = ltrim($interface, '\\');
            $concreteValue = ltrim($concrete, '\\');

            if ($interfaceKey === '' || $concreteValue === '') {
                continue;
            }

            $normalized[$interfaceKey] = $concreteValue;
        }

        return new self($normalized);
    }

    public function isEmpty(): bool
    {
        return $this->interfaceToConcrete === [];
    }

    public function hasBinding(string $interface, string $concrete): bool
    {
        $interfaceKey = ltrim($interface, '\\');
        $concreteValue = ltrim($concrete, '\\');

        return ($this->interfaceToConcrete[$interfaceKey] ?? null) === $concreteValue;
    }

    public function boundInterfaceForConcrete(string $concrete): ?string
    {
        $concreteValue = ltrim($concrete, '\\');

        foreach ($this->interfaceToConcrete as $interface => $boundConcrete) {
            if ($boundConcrete === $concreteValue) {
                return $interface;
            }
        }

        return null;
    }

    public function isConcreteBoundInContainer(string $concrete): bool
    {
        return $this->boundInterfaceForConcrete($concrete) !== null;
    }

    /**
     * @return BindingMap
     */
    public function all(): array
    {
        return $this->interfaceToConcrete;
    }
}
