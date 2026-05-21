# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.2] - Unreleased

### Added

- `ClassLineSpan` helper and `thresholds.fat_class.max_line_count` (default `200`, `0` disables) for per-class line span violations.
- `AnalysisResult` from `GuardAnalysisEngine::run()` with `filesScanned` count.
- `--stats` on `php artisan guard` to print scanned file count before results.
- `summary.files_scanned` in JSON output and human summary footer.
- `no_db_in_controller.exclude_path_prefixes` to skip database rule checks under chosen controller path prefixes.

### Fixed

- **missing-interface-binding** also skips `Laravel\*` framework types (in addition to `Illuminate\*`).

## [0.1.1] - 2026-05-21

### Added

- `ImportAliasMap` helper to resolve `use` statement aliases when analyzing AST nodes (including imports inside `namespace` blocks).

### Fixed

- **no-db-in-controller** now flags Eloquent static calls on imported short class names (e.g. `User::where()` with `use App\Models\User`).
- **missing-interface-binding** resolves imported short constructor types (e.g. `OrderRepository` → `App\Repositories\OrderRepository`).

### Changed

- PHPStan uses project-local cache directory (`.phpstan/cache`) to avoid `/tmp` lock failures in CI and pre-push hooks.

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

[0.1.1]: https://github.com/zaeem2396/LaraGuard/releases/tag/v0.1.1
[0.1.0]: https://github.com/zaeem2396/LaraGuard/releases/tag/v0.1.0
