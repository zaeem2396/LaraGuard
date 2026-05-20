# Changelog

All notable changes to this project will be documented in this file.

## Unreleased

### Added

- `ScanOutcome` return type from the scanner with diagnostic violations for missing scan roots and parse failures.
- Per-class analysis in `FatClassRule`, honoring `max_method_count` and `max_public_method_count`.
- Expanded `NoDbInControllerRule` for `DB` facade and Eloquent-style static calls (`error` severity).
- Minimal `MissingInterfaceBindingRule` for concrete `App\` constructor dependencies in Services/Repositories.
- Violation fixture app under `tests/fixtures/violations` with Pest coverage for rules, scanner diagnostics, and `--fail-on-error`.

### Changed

- `ScannerContract::scan()` now returns `ScanOutcome` instead of a bare file list.
- `NoDbInControllerRule` violations use `Severity::Error` so CI can fail via `--fail-on-error`.
