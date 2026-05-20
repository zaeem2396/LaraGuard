# Laravel Guard

[![Tests](https://github.com/zaeem2396/LaraGuard/actions/workflows/tests.yml/badge.svg)](https://github.com/zaeem2396/LaraGuard/actions/workflows/tests.yml)
[![Code style](https://github.com/zaeem2396/LaraGuard/actions/workflows/pint.yml/badge.svg)](https://github.com/zaeem2396/LaraGuard/actions/workflows/pint.yml)
[![Static analysis](https://github.com/zaeem2396/LaraGuard/actions/workflows/phpstan.yml/badge.svg)](https://github.com/zaeem2396/LaraGuard/actions/workflows/phpstan.yml)

**Laravel Guard** is a lightweight architectural linting toolkit for Laravel applications. It combines an AST-first scanner (via [`nikic/php-parser`](https://github.com/nikic/PHP-Parser)), a small rule engine, and a CLI experience that feels familiar if you already use Laravel Pint or PHPUnit.

## Highlights

- **Zero-config by default** with a publishable `config/guard.php`
- **Extensible rules** implementing `LaravelGuard\Guard\Contracts\RuleContract`
- **CI friendly** output, JSON mode, and explicit exit codes behind `--fail-on-error`
- **Future-proof seams** for caching, custom rules, SARIF, baselines, and deeper graphs

## Requirements

- PHP **8.3+**
- Laravel **11+** / **12+** / **13+** (Illuminate components `^11|^12|^13`)

## Quick start

```bash
composer require laravel-guard/laravel-guard
php artisan vendor:publish --tag=guard-config
php artisan guard
```

By default, Guard scans the `app` directory (configurable) and applies the bundled placeholder rules. Tighten CI pipelines with:

```bash
php artisan guard --fail-on-error
```

Structured automation output:

```bash
php artisan guard --format=json
```

## Configuration

Publish `config/guard.php` and adjust:

- **`rules`**: list of rule classes to execute (swap or append your own)
- **`paths`**: relative folders to scan (defaults to `app`)
- **`ignore`**: path prefixes skipped before parsing
- **`thresholds`**: per-rule tuning (for example `fat_class.max_method_count`)
- **`severity`**: `report_from` filters console noise; `fail_on` pairs with `--fail-on-error`

## Architecture (overview)

| Layer        | Responsibility |
| ------------ | -------------- |
| **Scanner**  | Recursively collects PHP files and parses them to AST nodes |
| **Rules**    | Inspect each `ScanFile` and emit `Violation` objects |
| **Engine**   | Orchestrates scanning + rule execution |
| **Renderer** | Formats human or JSON output with clear severity sections |

Design goals: keep classes small, lean on containers for resolution, and avoid regex-based “parsing”.

## Documentation

- [Installation](docs/installation.md)
- [Usage & CI snippets](docs/installation.md#usage)
- [Contributing](docs/contributing.md)

## License

The MIT License (MIT). See [LICENSE](LICENSE).
