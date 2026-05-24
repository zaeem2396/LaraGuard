# Configuration reference

Laravel Guard reads `config/guard.php` (merged automatically; publish with `vendor:publish --tag=guard-config`).

## Quick map

| Key | Purpose |
| --- | ------- |
| `rules` | Rule class FQCNs to load |
| `rule_options` | Per-rule `enabled` and `severity` overrides |
| `paths` | Directories to scan (see `GUARD_PATHS`) |
| `ignore` | Path prefixes and `.guardignore` patterns skipped by the scanner |
| `thresholds` | Rule-specific numeric limits |
| `severity` | Global output and exit-code thresholds |
| `no_db_in_controller` | Controller path excludes for DB rule |
| `provider_bindings` | Provider paths for container binding scan |
| `missing_interface_binding` | Strict mode for interface rule |

## Rules and `rule_options`

Register rule classes under `rules`:

```php
'rules' => [
    \LaravelGuard\Guard\Rules\NoDbInControllerRule::class,
    \LaravelGuard\Guard\Rules\FatClassRule::class,
    \LaravelGuard\Guard\Rules\MissingInterfaceBindingRule::class,
],
```

Toggle or tune each rule by **rule id** under `rule_options`:

```php
'rule_options' => [
    'no-db-in-controller' => [
        'enabled' => true,
        'severity' => 'error',
    ],
    'fat-class' => [
        'enabled' => false, // skip without removing the class from rules
    ],
    'missing-interface-binding' => [
        'enabled' => true,
        'severity' => 'info',
    ],
],
```

| Option | Values | Effect |
| ------ | ------ | ------ |
| `enabled` | `true` / `false` | When `false`, the rule is not registered and never runs |
| `severity` | `error`, `warning`, `info` | Overrides the severity emitted for that rule’s violations |

Omitted rules default to **enabled** with the rule’s built-in severity.

## Scan paths

```php
'paths' => [
    'app',
    'modules',
],
```

At least one non-empty path is required. An empty `paths` array throws a configuration error at boot.

### Environment: `GUARD_PATHS`

When configuration is published, you can override scan roots from `.env`:

```env
GUARD_PATHS=app,modules/Shared
```

Comma-separated, trimmed paths relative to the application base path.

## Ignored paths

Guard skips files matched by **either**:

1. **`ignore` in `config/guard.php`** — prefix-style paths (backward compatible).
2. **`.guardignore` at the project root** — gitignore-like patterns with optional globs and negation.

Patterns from both sources are merged. Later matching rules win (same as gitignore), so you can exclude a subtree and re-include specific files with `!`.

```php
'ignore' => [
    'bootstrap/cache',
    'storage',
    'vendor',
    'tests',
],
```

Example `.guardignore`:

```gitignore
# Legacy controllers — still scanned unless negated
app/Http/Controllers/Legacy/**

# Re-include one file for gradual migration
!app/Http/Controllers/Legacy/ImportantController.php
```

Supported pattern features:

| Syntax | Meaning |
| ------ | ------- |
| `app/Legacy` | Match path prefix or segment |
| `app/Legacy/` | Match directory and descendants |
| `*` | Match within one path segment |
| `**` | Match across segments |
| `!path` | Negate — scan this path even if a prior rule ignored it |
| `# comment` | Ignored line |

Publish a starter file:

```bash
php artisan vendor:publish --tag=guard-ignore
```

## Severity and CI exit codes

```php
'severity' => [
    'report_from' => 'info',   // minimum severity shown in output
    'fail_on' => 'error',      // minimum severity that fails --fail-on-error
],
```

### Environment: `GUARD_FAIL_ON`

```env
GUARD_FAIL_ON=error
```

Accepted values: `error`, `warning`, `info`.

## Thresholds example

```php
'thresholds' => [
    'fat_class' => [
        'max_method_count' => 20,
        'max_public_method_count' => 12,
        'max_line_count' => 200,
    ],
],
```

Set `max_line_count` to `0` to disable line-span checks.

## Rule-specific blocks

See [rules.md](rules.md) for behavior. Config keys:

- `no_db_in_controller.exclude_path_prefixes`
- `provider_bindings.scan_paths`
- `missing_interface_binding.strict`

## Related docs

- [installation.md](installation.md) — install and publish
- [rules.md](rules.md) — rule semantics and JSON output
- [ci.md](ci.md) — pipeline examples
