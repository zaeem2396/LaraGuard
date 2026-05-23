# Contributing

Thanks for helping improve Laravel Guard. The project favors **clear architecture**, **predictable extension points**, and **developer ergonomics**.

## Tooling

| Command | Purpose |
| ------- | ------- |
| `composer lint` | Laravel Pint (`pint.json`; test fixtures excluded) |
| `composer analyse` | PHPStan + Larastan (`phpstan.neon.dist`, cache in `.phpstan/cache`) |
| `composer test` | Pest + Orchestra Testbench |
| `composer security-audit` | Composer security audit |
| `composer validate-package` | Strict `composer.json` validation |
| `composer pre-push` | Runs lint, analyse, test, audit, and validate |

## Workflow

1. Fork and branch from `main` (e.g. `feature/v0.2.0-layer-rules`).
2. Keep commits focused; match existing message style.
3. Run `composer lint`, `composer analyse`, and `composer test` locally.
4. Open a pull request with summary, test plan, and any roadmap impact.

## Tests

- **Testbench** boots the package via `tests/TestCase.php`.
- **Fixtures** live under `tests/fixtures/violations/` (sample `app/` tree with intentional violations).
- When adding or changing rules, add or update Pest tests under `tests/Unit/` or `tests/Feature/` and extend fixtures as needed.

Do not run Pint on `tests/fixtures/` (intentionally invalid PHP may exist for parser diagnostics).

## Code expectations

- `declare(strict_types=1);` on every new PHP file.
- Prefer `readonly` classes and properties when mutation is unnecessary.
- Keep rules and scanners cohesive; add contracts only when multiple implementations are likely.
- Prefer AST analysis (`nikic/php-parser`) over regex for PHP structure.
- Mark roadmap items **Partial** until behavior is tested and documented in [rules.md](rules.md).
- Container binding behavior lives in `ServiceProviderBindingScanner` and `ContainerBindingMap`; update tests under `tests/fixtures/provider-bindings/` when extending binding detection.

## Documentation

When changing behavior, update:

- [rules.md](rules.md) — rule semantics and limits
- [installation.md](installation.md) — install and usage
- [ci.md](ci.md) — pipeline examples
- [README.md](../README.md) — high-level summary
- [CHANGELOG.md](../CHANGELOG.md) — user-facing changes

## Questions

Open a GitHub issue with reproduction steps for bugs or a short design sketch for features.
