# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## Unreleased

### Added

- `ScanOutcome` from the scanner with diagnostic violations for missing scan roots and parse failures.
- Per-class `FatClassRule` analysis using `max_method_count` and `max_public_method_count`.
- Expanded `NoDbInControllerRule` for `DB` facade and Eloquent-style static calls in controllers (`error` severity).
- `MissingInterfaceBindingRule` for concrete `App\` constructor types in Services and Repositories.
- Test fixtures under `tests/fixtures/violations` and Pest coverage for rules, scanner diagnostics, and `--fail-on-error`.
- Documentation: [rules.md](docs/rules.md), [ci.md](docs/ci.md).

### Changed

- `ScannerContract::scan()` returns `ScanOutcome` (files + diagnostics) instead of a bare file list.
- `NoDbInControllerRule` violations use `Severity::Error` so `--fail-on-error` fails CI when triggered.
- README and installation docs updated for bundled rules, severity, and CI behavior.

### Known limitations

- Eloquent calls via imported short class names (without FQCN in the AST) are not detected yet.
- No Packagist release or semver tag for v0.1.0 yet.
- No SARIF, baselines, `.guardignore`, or layer rules (see roadmap).

## [0.1.0] — TBD

Initial architectural guard foundation: `php artisan guard`, config, bundled rules, and package CI.
