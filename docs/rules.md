# Rules reference

Laravel Guard ships with a small set of AST-based rules. Each rule implements `LaravelGuard\Guard\Contracts\RuleContract` and returns zero or more `Violation` objects per scanned file.

Enable or disable rules by editing the `rules` array in `config/guard.php`.

## Bundled rules

| Rule ID | Class | Default severity | What it checks |
| ------- | ----- | ---------------- | -------------- |
| `no-db-in-controller` | `NoDbInControllerRule` | **error** | Database access inside `Http/Controllers` |
| `fat-class` | `FatClassRule` | **info** | Classes exceeding method, public-method, or line-span thresholds |
| `missing-interface-binding` | `MissingInterfaceBindingRule` | **info** | Concrete `App\` types in constructors under Services/Repositories (container-aware) |

The scanner may also emit diagnostics with rule id `scanner` (e.g. missing scan roots, unparseable PHP files). Those use **warning** severity.

## `no-db-in-controller`

**Goal:** Keep HTTP controllers thin; persistence belongs in actions, services, or repositories.

**Detects (in controller paths):**

- `DB::…` static calls (including `Illuminate\Support\Facades\DB`)
- Eloquent-style static calls on models, including via `use` imports (e.g. `use App\Models\User` then `User::where()`)

**CI:** Because violations are **errors**, `php artisan guard --fail-on-error` exits with code `1` when this rule fires.

**Exclude paths:** Skip specific controller subtrees via `no_db_in_controller.exclude_path_prefixes` (path prefixes relative to the app root):

```php
'no_db_in_controller' => [
    'exclude_path_prefixes' => [
        'app/Http/Controllers/Api/V1',
    ],
],
```

**Does not detect:** Instance-level persistence (`$user->save()`) — planned for a later release.

## `fat-class`

**Goal:** Highlight classes that are growing too large to maintain.

**Detects:**

- Per **class** in a file (not per file totals)
- Non-abstract method count vs `thresholds.fat_class.max_method_count` (default `20`)
- Public method count vs `thresholds.fat_class.max_public_method_count` (default `12`)
- Inclusive line span (AST start/end lines) vs `thresholds.fat_class.max_line_count` (default `200`, `0` disables)

**Configure** in `config/guard.php`:

```php
'thresholds' => [
    'fat_class' => [
        'max_method_count' => 20,
        'max_public_method_count' => 12,
        'max_line_count' => 200,
    ],
],
```

## `missing-interface-binding`

**Goal:** Encourage binding interfaces in the container instead of type-hinting concrete application classes.

**Detects:**

- Concrete constructor parameter types under `app/Services` and `app/Repositories` (path segments `Services/`, `Repositories/`)
- Resolves `use` imports so short type names (e.g. `OrderRepository`) map to `App\…` classes

**Skips:**

- Parameters already typed as `*Interface`
- `Illuminate\*` and `Laravel\*` framework types
- Concrete types that are registered as the implementation of an interface in a scanned service provider (see [Container binding graph](#container-binding-graph-phase-1))

**Strict mode** (`missing_interface_binding.strict`, default `false`):

When enabled, emits an **info** violation if the constructor type-hints a concrete class that already has a container binding to an interface — nudging you to type-hint the interface instead.

```php
'missing_interface_binding' => [
    'strict' => true,
],
```

## Container binding graph (phase 1)

Before evaluating services and repositories, Guard scans configured provider paths (default `app/Providers`) and builds a map from **interface FQCN → concrete FQCN** by parsing:

- `$this->app->bind(Interface::class, Concrete::class)`
- `$this->app->singleton(…)` and `$this->app->scoped(…)` with the same `::class` argument shape

Configure scan roots:

```php
'provider_bindings' => [
    'scan_paths' => [
        'app/Providers',
    ],
],
```

**Phase 1 limitations:**

- Only `SomeClass::class` arguments are recognized (not string literals or variables).
- Bindings must appear as `$this->app->bind|singleton|scoped` calls.
- Closures, `instance()`, attribute discovery, and package auto-registration are not analyzed.
- The map is rebuilt on every `php artisan guard` run (no persistent cache yet).

## JSON output contract

When `--format=json` is used, the top-level object includes a stable schema version and fixed keys for CI consumers.

| Key | Type | Description |
| --- | ---- | ----------- |
| `version` | string | Schema version (currently `"1"`) |
| `errors` | array | Violations with severity **error** |
| `warnings` | array | Violations with severity **warning** |
| `info` | array | Violations with severity **info** |
| `summary` | object | Aggregate counts |

Each violation object contains:

| Key | Type | Description |
| --- | ---- | ----------- |
| `severity` | string | `error`, `warning`, or `info` |
| `file` | string | Project-relative path |
| `line` | int | Line number (`0` for file-level scanner diagnostics) |
| `message` | string | Human-readable description |
| `suggestion` | string\|null | Optional remediation hint |
| `rule_id` | string\|null | Rule identifier (e.g. `no-db-in-controller`, `scanner`) |

The `summary` object contains:

| Key | Type | Description |
| --- | ---- | ----------- |
| `errors` | int | Count of error violations |
| `warnings` | int | Count of warning violations |
| `info` | int | Count of info violations |
| `files_scanned` | int | Number of PHP files analyzed |

New fields may be added in future schema versions; consumers should read `version` before parsing.

## CLI options

| Flag | Purpose |
| ---- | ------- |
| `--stats` | Print how many files were scanned before results |
| `--format=json` | JSON output using schema version `1` (see [JSON output contract](#json-output-contract)) |

## Severity and CI

| Config key | Purpose |
| ---------- | ------- |
| `severity.report_from` | Minimum severity shown in output (`info`, `warning`, `error`) |
| `severity.fail_on` | Minimum severity that triggers exit code `1` with `--fail-on-error` |

Example: with defaults (`report_from: info`, `fail_on: error`), only **error** violations fail CI; **info** and **warning** are reported but do not fail the build.

## Custom rules

1. Implement `RuleContract` (method `id(): string`, `evaluate(ScanFile $file): array`).
2. Register the class FQCN in `config/guard.php` under `rules`.
3. Use the container for dependencies (e.g. `GuardConfig`) in the rule constructor.

See [installation.md](installation.md#custom-rules) for a minimal example.
