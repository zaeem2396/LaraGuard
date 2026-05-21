# Installation

## Composer

```bash
composer require laravel-guard/laravel-guard
```

Laravel auto-discovers `LaravelGuard\Guard\GuardServiceProvider`.

> **Note:** The package name in `composer.json` is `laravel-guard/laravel-guard`. Until the package is published on Packagist, install from your VCS repository or path as documented by your team.

## Zero-config usage

After install, run:

```bash
php artisan guard
```

Guard merges default configuration, scans the `app` directory (relative to your application base path), and runs bundled rules. No publish step is required for defaults.

If a configured scan root does not exist (for example before `app/` is created), Guard reports a **warning** diagnostic and continues with other roots.

## Publish configuration (optional)

```bash
php artisan vendor:publish --tag=guard-config
```

This copies `config/guard.php` where you can:

- Add or remove rule classes under `rules`
- Change `paths` and `ignore`
- Tune `thresholds` and `severity`

## Requirements

- PHP `^8.3`
- Laravel `11.x`, `12.x`, or `13.x` (Illuminate Console, Contracts, and Support)

## Usage

### Local

```bash
php artisan guard
php artisan guard --format=json
php artisan guard --fail-on-error
```

### CI integration

Use `--fail-on-error` so the process exits with code `1` when violations meet `severity.fail_on` (default: **error**). Controller database violations are **errors**, so they fail typical pipelines.

```yaml
- name: Architectural guard
  run: php artisan guard --fail-on-error
```

More examples: [ci.md](ci.md).

### JSON output

```bash
php artisan guard --format=json
```

Response shape:

```json
{
  "errors": [],
  "warnings": [],
  "info": [],
  "summary": { "errors": 0, "warnings": 0, "info": 0 }
}
```

Each violation includes `severity`, `file`, `line`, `message`, `suggestion`, and `rule_id`.

## Custom rules

1. Create a class implementing `LaravelGuard\Guard\Contracts\RuleContract`.
2. Add its FQCN to `rules` in `config/guard.php`.
3. Inject dependencies via the constructor (resolved by the container).

```php
<?php

declare(strict_types=1);

namespace App\Guard\Rules;

use LaravelGuard\Guard\Contracts\RuleContract;
use LaravelGuard\Guard\Support\ScanFile;

final readonly class MyRule implements RuleContract
{
    public function id(): string
    {
        return 'my-rule';
    }

    public function evaluate(ScanFile $file): array
    {
        return [];
    }
}
```

Rule behavior and bundled rules: [rules.md](rules.md).

## Scanner diagnostics

Besides rule violations, the scanner may report:

- Missing scan roots (warning)
- Unparseable PHP files (warning, `rule_id: scanner`)

These appear in the same CLI sections and JSON payload as rule output.

## Versioning

Install a specific release from GitHub:

```bash
composer require laravel-guard/laravel-guard:v0.1.1
```

See [releases](https://github.com/zaeem2396/LaraGuard/releases) for changelog notes.

## Roadmap

Future releases may add baselines, SARIF, `.guardignore`, layer rules, and persistent scan caching. See project issues for status.
