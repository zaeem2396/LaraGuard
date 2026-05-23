# Laravel Guard

[![Tests](https://github.com/zaeem2396/LaraGuard/actions/workflows/tests.yml/badge.svg)](https://github.com/zaeem2396/LaraGuard/actions/workflows/tests.yml)
[![Code style](https://github.com/zaeem2396/LaraGuard/actions/workflows/pint.yml/badge.svg)](https://github.com/zaeem2396/LaraGuard/actions/workflows/pint.yml)
[![Static analysis](https://github.com/zaeem2396/LaraGuard/actions/workflows/phpstan.yml/badge.svg)](https://github.com/zaeem2396/LaraGuard/actions/workflows/phpstan.yml)

**Laravel Guard** is a lightweight architectural linting toolkit for Laravel applications. It combines an AST-first scanner (via [`nikic/php-parser`](https://github.com/nikic/PHP-Parser)), a small rule engine, and a CLI experience that feels familiar if you already use Laravel Pint or PHPUnit.

**Latest release:** [v0.1.3](https://github.com/zaeem2396/LaraGuard/releases/tag/v0.1.3) — container-aware interface binding checks with optional strict mode.

## Highlights

- **Zero-config by default** — scans `app/` with sensible ignores after install
- **Bundled rules** for controllers, class size, and container-aware constructor bindings
- **CI friendly** — JSON output and non-zero exits via `--fail-on-error`
- **Extensible** — implement `RuleContract` and register classes in config
- **Future-proof seams** for caching, SARIF, baselines, and dependency graphs

## Requirements

- PHP **8.3+**
- Laravel **11+** / **12+** / **13+** (Illuminate components `^11|^12|^13`)

## Quick start

```bash
composer require laravel-guard/laravel-guard
php artisan guard
```

Optional: publish and customize configuration.

```bash
php artisan vendor:publish --tag=guard-config
```

Fail CI when **error**-level violations are found (including database usage in controllers):

```bash
php artisan guard --fail-on-error
```

Machine-readable output:

```bash
php artisan guard --format=json
php artisan guard --stats
```

## Bundled rules (summary)

| Rule | Severity | Purpose |
| ---- | -------- | ------- |
| `no-db-in-controller` | error | No `DB` facade or Eloquent static queries in HTTP controllers |
| `fat-class` | info | Classes over method, public-method, or line-span limits |
| `missing-interface-binding` | info | Concrete `App\` constructor types in Services / Repositories (container-aware) |

See [docs/rules.md](docs/rules.md) for behavior, limits, and configuration.

## Configuration

Publish `config/guard.php` and adjust:

- **`rules`** — rule class FQCNs to run
- **`paths`** — directories to scan (default: `app`)
- **`ignore`** — path prefixes to skip
- **`thresholds`** — per-rule limits (e.g. `fat_class.max_line_count`)
- **`no_db_in_controller`** — optional controller path prefixes to exclude from DB checks
- **`provider_bindings`** — paths scanned for interface → concrete container bindings
- **`missing_interface_binding.strict`** — flag concrete type-hints when a binding already exists
- **`severity`** — `report_from` and `fail_on` for output and exit codes

## Architecture

| Layer | Responsibility |
| ----- | -------------- |
| **Scanner** | Walks configured paths, parses PHP to AST, emits scan diagnostics |
| **Binding scan** | Parses service providers for `bind` / `singleton` / `scoped` interface maps |
| **Rules** | Inspect each `ScanFile` and return `Violation` instances |
| **Engine** | Merges scanner diagnostics with rule results |
| **Renderer** | Human (Errors / Warnings / Info) or JSON output |

Design goals: small classes, container-driven wiring, no regex-based PHP parsing.

## Documentation

- [Installation](docs/installation.md)
- [Rules reference](docs/rules.md)
- [CI integration](docs/ci.md)
- [Contributing](docs/contributing.md)

## License

The MIT License (MIT). See [LICENSE](LICENSE).
