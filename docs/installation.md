# Installation

## Composer

```bash
composer require laravel-guard/laravel-guard
```

Laravel will auto-discover `LaravelGuard\Guard\GuardServiceProvider`.

## Publish configuration (optional)

```bash
php artisan vendor:publish --tag=guard-config
```

This copies `config/guard.php` where you can enable/disable rules, tweak paths, ignores, thresholds, and severity behavior.

## Requirements

- PHP `^8.3`
- Laravel `11.x` / `12.x` (Illuminate Console, Contracts, and Support components)

## Usage

Run locally:

```bash
php artisan guard
```

### CI integration

```yaml
- name: Architectural guard
  run: php artisan guard --fail-on-error
```

### JSON consumers

```bash
php artisan guard --format=json
```

The payload groups violations under `errors`, `warnings`, and `info`, with a numeric `summary`.

## Custom rules

Create a class that implements `LaravelGuard\Guard\Contracts\RuleContract`, register it in `config/guard.php` under `rules`, and resolve any dependencies through the container constructor.

## Roadmap hooks

The default scanner ships with a `ScanCacheContract` seam so future releases can layer incremental analysis, baselines, SARIF exporters, or IDE integrations without breaking the public CLI.
