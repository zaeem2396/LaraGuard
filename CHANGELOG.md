# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.1] - Unreleased

### Added

- `ImportAliasMap` helper to resolve `use` statement aliases when analyzing AST nodes.

### Fixed

- **no-db-in-controller** now flags Eloquent static calls on imported short class names (e.g. `User::where()` with `use App\Models\User`).
- **missing-interface-binding** resolves imported short constructor types (e.g. `OrderRepository` → `App\Repositories\OrderRepository`).

## [0.1.0] - 2026-05-20

First public release: architectural linting for Laravel via `php artisan guard`.

### Added

- Laravel package with auto-discovered `GuardServiceProvider` and publishable `config/guard.php`.
- `php artisan guard` with human output (Errors / Warnings / Info) and `--format=json`.
- `--fail-on-error` exit codes driven by `severity.fail_on` (default: **error**).
- AST scanning with `nikic/php-parser` and `ScanOutcome` (parsed files + scanner diagnostics).
- Scanner warnings for missing scan roots and unparseable PHP files.
- Rule engine: `RuleContract`, `RuleRegistry`, and `Violation` model with severity levels.
- **no-db-in-controller** — flags `DB` facade and Eloquent static usage in HTTP controllers (**error**).
- **fat-class** — per-class method and public-method thresholds from config.
- **missing-interface-binding** — concrete `App\` constructor dependencies in Services/Repositories.
- Pest + Orchestra Testbench suite with violation fixtures under `tests/fixtures/violations`.
- GitHub Actions: Pint, PHPStan (Larastan), Pest matrix (PHP 8.3–8.5, Laravel 11–13), Composer audit, prefer-lowest.
- Documentation: [README](README.md), [installation](docs/installation.md), [rules](docs/rules.md), [CI](docs/ci.md), [contributing](docs/contributing.md).

### Changed

- `ScannerContract::scan()` returns `ScanOutcome` instead of a bare file list.

### Requirements

- PHP ^8.3
- Laravel 11, 12, or 13 (Illuminate Console, Contracts, Support)

### Known limitations

- No `.guardignore`, SARIF output, baselines, or layer rules (planned for later releases).
- Install from VCS until Packagist publication (see [installation](docs/installation.md)).

[0.1.0]: https://github.com/zaeem2396/LaraGuard/releases/tag/v0.1.0
