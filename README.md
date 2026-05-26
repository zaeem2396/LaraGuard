# Laravel Guard

[![Tests](https://github.com/zaeem2396/LaraGuard/actions/workflows/tests.yml/badge.svg)](https://github.com/zaeem2396/LaraGuard/actions/workflows/tests.yml)
[![Code style](https://github.com/zaeem2396/LaraGuard/actions/workflows/pint.yml/badge.svg)](https://github.com/zaeem2396/LaraGuard/actions/workflows/pint.yml)
[![Static analysis](https://github.com/zaeem2396/LaraGuard/actions/workflows/phpstan.yml/badge.svg)](https://github.com/zaeem2396/LaraGuard/actions/workflows/phpstan.yml)

**Laravel Guard** is a lightweight architectural linting toolkit for Laravel applications. It combines an AST-first scanner (via [`nikic/php-parser`](https://github.com/nikic/PHP-Parser)), a small rule engine, and a CLI experience that feels familiar if you already use Laravel Pint or PHPUnit.

**Latest release:** [v0.2.1](https://github.com/zaeem2396/LaraGuard/releases/tag/v0.2.1) — `.guardignore` glob patterns, negation, and merged config ignores.

## Highlights

- **Zero-config by default** — scans `app/` with sensible ignores after install; optional `.guardignore` for globs and negation
- **Bundled rules** for controllers, class size, and container-aware constructor bindings
- **CI friendly** — JSON output and non-zero exits via `--fail-on-error`
- **Extensible** — implement `RuleContract` and register classes in config
- **Future-proof seams** for caching, SARIF, baselines, and dependency graphs

## Requirements

- PHP **8.3+**
- Laravel **11+** / **12+** / **13+** (Illuminate components `^11|^12|^13`)

## Version compatibility

Maintainer CI runs against the matrix below. Your app should use a supported PHP and Laravel pair.

| PHP | Laravel 11 | Laravel 12 | Laravel 13 |
| --- | ---------- | ---------- | ---------- |
| 8.3 | Supported | Supported | — |
| 8.4 | Supported | Supported | — |
| 8.5 | — | — | Supported |

Illuminate component constraints in `composer.json`: `^11.0|^12.0|^13.0`.

## Quick start

```bash
composer require laravel-guard/laravel-guard
php artisan guard
```

Optional: publish and customize configuration.

```bash
php artisan vendor:publish --tag=guard-config
php artisan vendor:publish --tag=guard-ignore   # optional glob ignore file
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
- **`rule_options`** — enable/disable rules and override per-rule severity
- **`severity`** — `report_from` and `fail_on` for output and exit codes (supports `GUARD_FAIL_ON`)

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
- [Configuration](docs/configuration.md)
- [CI integration](docs/ci.md)
- [Contributing](docs/contributing.md)
- [Security policy](SECURITY.md)

## License

The MIT License (MIT). See [LICENSE](LICENSE).
