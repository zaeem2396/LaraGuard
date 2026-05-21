# Rules reference

Laravel Guard ships with a small set of AST-based rules. Each rule implements `LaravelGuard\Guard\Contracts\RuleContract` and returns zero or more `Violation` objects per scanned file.

Enable or disable rules by editing the `rules` array in `config/guard.php`.

## Bundled rules

| Rule ID | Class | Default severity | What it checks |
| ------- | ----- | ---------------- | -------------- |
| `no-db-in-controller` | `NoDbInControllerRule` | **error** | Database access inside `Http/Controllers` |
| `fat-class` | `FatClassRule` | **info** | Classes exceeding configured method counts |
| `missing-interface-binding` | `MissingInterfaceBindingRule` | **info** | Concrete `App\` types in constructors under Services/Repositories |

The scanner may also emit diagnostics with rule id `scanner` (e.g. missing scan roots, unparseable PHP files). Those use **warning** severity.

## `no-db-in-controller`

**Goal:** Keep HTTP controllers thin; persistence belongs in actions, services, or repositories.

**Detects (in controller paths):**

- `DB::…` static calls (including `Illuminate\Support\Facades\DB`)
- Eloquent-style static calls on models, including via `use` imports (e.g. `use App\Models\User` then `User::where()`)

**CI:** Because violations are **errors**, `php artisan guard --fail-on-error` exits with code `1` when this rule fires.

## `fat-class`

**Goal:** Highlight classes that are growing too large to maintain.

**Detects:**

- Per **class** in a file (not per file totals)
- Non-abstract method count vs `thresholds.fat_class.max_method_count` (default `20`)
- Public method count vs `thresholds.fat_class.max_public_method_count` (default `12`)

**Configure** in `config/guard.php`:

```php
'thresholds' => [
    'fat_class' => [
        'max_method_count' => 20,
        'max_public_method_count' => 12,
    ],
],
```

Line-count thresholds are not implemented yet.

## `missing-interface-binding`

**Goal:** Encourage binding interfaces in the container instead of type-hinting concrete application classes.

**Detects:**

- Concrete constructor parameter types under `app/Services` and `app/Repositories` (path segments `Services/`, `Repositories/`)
- Resolves `use` imports so short type names (e.g. `OrderRepository`) map to `App\…` classes

**Does not yet detect:**

- Whether a matching interface is already bound in a service provider

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
