<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Scanners;

use Illuminate\Contracts\Foundation\Application;
use LaravelGuard\Guard\Config\GuardConfig;
use LaravelGuard\Guard\Support\Binding\ClassConstFqcnResolver;
use LaravelGuard\Guard\Support\ContainerBindingMap;
use LaravelGuard\Guard\Support\ImportAliasMap;
use PhpParser\Error as PhpParserError;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\VariadicPlaceholder;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;

final readonly class ServiceProviderBindingScanner
{
    private const array BINDING_METHODS = [
        'bind',
        'singleton',
        'scoped',
    ];

    public function __construct(
        private Application $application,
        private GuardConfig $config,
    ) {}

    public function scan(): ContainerBindingMap
    {
        $parser = (new ParserFactory)->createForHostVersion();
        $bindings = [];

        foreach ($this->config->providerBindingScanPaths as $relativeRoot) {
            $absoluteRoot = $this->application->basePath($relativeRoot);

            if (! is_dir($absoluteRoot)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absoluteRoot, \FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $item) {
                if (! $item instanceof \SplFileInfo || ! $item->isFile()) {
                    continue;
                }

                if ($item->getExtension() !== 'php') {
                    continue;
                }

                $path = $item->getPathname();
                $contents = @file_get_contents($path);

                if ($contents === false) {
                    continue;
                }

                try {
                    $statements = $parser->parse($contents);
                } catch (PhpParserError) {
                    continue;
                }

                if (! is_array($statements)) {
                    continue;
                }

                /** @var list<Stmt> $statementList */
                $statementList = $statements;
                $importMap = ImportAliasMap::fromStatements($statementList);

                foreach ($this->extractBindings($statementList, $importMap) as $interface => $concrete) {
                    $bindings[$interface] = $concrete;
                }
            }
        }

        return ContainerBindingMap::fromArray($bindings);
    }

    /**
     * @param  list<Stmt>  $statements
     * @param  array<string, string>  $importMap
     * @return array<string, string>
     */
    private function extractBindings(array $statements, array $importMap): array
    {
        $bindings = [];
        $finder = new NodeFinder;

        foreach ($finder->findInstanceOf($statements, StaticCall::class) as $call) {
            if (! $call instanceof StaticCall) {
                continue;
            }

            $pair = $this->resolveBindingPair($call, $importMap);

            if ($pair !== null) {
                $bindings[$pair['interface']] = $pair['concrete'];
            }
        }

        foreach ($finder->findInstanceOf($statements, MethodCall::class) as $call) {
            if (! $call instanceof MethodCall) {
                continue;
            }

            $pair = $this->resolveBindingPairFromMethodCall($call, $importMap);

            if ($pair !== null) {
                $bindings[$pair['interface']] = $pair['concrete'];
            }
        }

        return $bindings;
    }

    /**
     * @param  array<string, string>  $importMap
     * @return array{interface: string, concrete: string}|null
     */
    private function resolveBindingPair(StaticCall $call, array $importMap): ?array
    {
        if (! $this->isContainerBindingCall($call->name, $call->class)) {
            return null;
        }

        return $this->pairFromArguments($call->args, $importMap);
    }

    /**
     * @param  array<string, string>  $importMap
     * @return array{interface: string, concrete: string}|null
     */
    private function resolveBindingPairFromMethodCall(MethodCall $call, array $importMap): ?array
    {
        if (! $this->isAppPropertyBindingCall($call)) {
            return null;
        }

        return $this->pairFromArguments($call->args, $importMap);
    }

    private function isContainerBindingCall(mixed $name, mixed $class): bool
    {
        if (! $name instanceof Identifier) {
            return false;
        }

        if (! in_array($name->toString(), self::BINDING_METHODS, true)) {
            return false;
        }

        if (! $class instanceof PropertyFetch) {
            return false;
        }

        if (! $class->var instanceof Variable || $class->var->name !== 'this') {
            return false;
        }

        if (! $class->name instanceof Identifier || $class->name->toString() !== 'app') {
            return false;
        }

        return true;
    }

    private function isAppPropertyBindingCall(MethodCall $call): bool
    {
        if (! $call->name instanceof Identifier) {
            return false;
        }

        if (! in_array($call->name->toString(), self::BINDING_METHODS, true)) {
            return false;
        }

        if (! $call->var instanceof PropertyFetch) {
            return false;
        }

        if (! $call->var->var instanceof Variable || $call->var->var->name !== 'this') {
            return false;
        }

        return $call->var->name instanceof Identifier && $call->var->name->toString() === 'app';
    }

    /**
     * @param  array<Arg|VariadicPlaceholder>  $args
     * @param  array<string, string>  $importMap
     * @return array{interface: string, concrete: string}|null
     */
    private function pairFromArguments(array $args, array $importMap): ?array
    {
        $positional = array_values(array_filter(
            $args,
            static fn (mixed $arg): bool => $arg instanceof Arg,
        ));

        if (count($positional) < 2) {
            return null;
        }

        $interface = ClassConstFqcnResolver::resolve($positional[0]->value, $importMap);
        $concrete = ClassConstFqcnResolver::resolve($positional[1]->value, $importMap);

        if ($interface === null || $concrete === null) {
            return null;
        }

        return [
            'interface' => $interface,
            'concrete' => $concrete,
        ];
    }
}
