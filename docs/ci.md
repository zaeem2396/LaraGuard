# Continuous integration

## Running Guard in your Laravel app

Add a step after Composer install and (optionally) configuration publish:

```yaml
- name: Install dependencies
  run: composer install --no-interaction --prefer-dist

- name: Architectural guard
  run: php artisan guard --fail-on-error
```

Guard exits `0` when no violations meet `severity.fail_on` (default: **error**, overridable via `GUARD_FAIL_ON` in `.env`). Controller database usage from `no-db-in-controller` is reported as **error**, so CI fails when that rule triggers.

Disable rules or tune severities in `config/guard.php` — see [configuration.md](configuration.md).

Exclude legacy trees from scans with a root `.guardignore` file (`vendor:publish --tag=guard-ignore`). Patterns merge with config `ignore` — see [configuration.md](configuration.md#ignored-paths).

The **layer-violation** rule defaults to **warning** severity, so it does not fail `--fail-on-error` unless you override `rule_options` or `GUARD_FAIL_ON` — see [rules.md](rules.md#layer-violation).

## JSON output for tooling

```yaml
- name: Guard (JSON)
  run: php artisan guard --format=json > guard-report.json
```

The JSON object includes `version`, `errors`, `warnings`, `info`, and `summary` (including `files_scanned`). See [rules.md](rules.md#json-output-contract).

Optional scan statistics before human output:

```bash
php artisan guard --stats
```

## This package repository

Workflows under `.github/workflows/` validate **the Guard package itself** (Pint, PHPStan, Pest across PHP 8.3–8.5 and Laravel 11–13). They do **not** run `php artisan guard` against a sample Laravel application.

A dedicated consumer workflow template for downstream apps is planned for v0.3.0.

## Bitbucket Pipelines

Example (adjust PHP image as needed):

```yaml
pipelines:
  default:
    - step:
        name: Guard
        image: php:8.3-cli
        script:
          - composer install --no-interaction --prefer-dist
          - php artisan guard --fail-on-error
```

Bitbucket examples will be expanded in a future release.
