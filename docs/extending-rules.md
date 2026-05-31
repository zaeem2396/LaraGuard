# Extending Laravel Guard with custom rules

Third-party packages and application code can register additional rules at runtime without editing `config/guard.php`.

## Quick start

1. Implement `RuleContract` (or extend `AbstractRule` for convention-based ids).
2. Register the rule from your `AppServiceProvider` or package service provider.
3. Optionally tune the rule via `rule_options.{rule-id}` in config.

```php
use LaravelGuard\Guard\Facades\Guard;
use LaravelGuard\Guard\Rules\AbstractRule;

final class ForbiddenWordRule extends AbstractRule
{
    public function evaluate(ScanFile $file): array
    {
        // Return zero or more Violation instances.
    }
}
```

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    Guard::extend(ForbiddenWordRule::class);
}
```

Run `php artisan guard` — your rule executes alongside bundled rules.

## `Guard::extend()`

Register a rule class that implements `LaravelGuard\Guard\Contracts\RuleContract`:

```php
Guard::extend(\Acme\Guard\Rules\NoLegacyImportsRule::class);
```

- Merged with classes listed in `config/guard.php` `rules`
- Resolved through the Laravel container (constructor dependencies are injected)
- Respects `rule_options.{rule-id}.enabled` and severity overrides
- Safe to call from `boot()`; if the registry was already built, Guard rebuilds it automatically

## `Guard::booting()`

Package service providers can defer registration until the application has finished booting:

```php
// In your package register() or boot()
Guard::booting(function (): void {
    Guard::extend(\Acme\Guard\Rules\NoLegacyImportsRule::class);
});
```

Callbacks run once after all service providers boot.

## Convention-based rule ids

Extend `LaravelGuard\Guard\Rules\AbstractRule` to derive the rule id from the class name:

| Class | Rule id |
| ----- | ------- |
| `ForbiddenWordRule` | `forbidden-word` |
| `AcmeLayerRule` | `acme-layer` |

Disable or override severity in config:

```php
'rule_options' => [
    'forbidden-word' => [
        'enabled' => false,
    ],
    'acme-layer' => [
        'severity' => 'error',
    ],
],
```

Implement `id()` yourself when you need a stable id that does not follow the class name.

## Example package: `laravel-guard-acme`

Minimal structure for a publishable Guard rules package:

```
laravel-guard-acme/
├── composer.json
└── src/
    ├── AcmeGuardServiceProvider.php
    └── Rules/
        └── NoLegacyImportsRule.php
```

**composer.json**

```json
{
    "name": "acme/laravel-guard-acme",
    "require": {
        "php": "^8.3",
        "laravel-guard/laravel-guard": "^0.2.3"
    },
    "autoload": {
        "psr-4": {
            "Acme\\Guard\\": "src/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Acme\\Guard\\AcmeGuardServiceProvider"
            ]
        }
    }
}
```

**src/Rules/NoLegacyImportsRule.php**

```php
namespace Acme\Guard\Rules;

use LaravelGuard\Guard\Rules\AbstractRule;
use LaravelGuard\Guard\Support\ImportAliasMap;
use LaravelGuard\Guard\Support\ScanFile;
use LaravelGuard\Guard\Violations\Severity;
use LaravelGuard\Guard\Violations\Violation;

final class NoLegacyImportsRule extends AbstractRule
{
    public function evaluate(ScanFile $file): array
    {
        $imports = ImportAliasMap::fromStatements($file->statements);

        foreach ($imports as $fqcn) {
            if (str_starts_with($fqcn, 'App\\Legacy\\')) {
                return [
                    new Violation(
                        severity: Severity::Warning,
                        file: $file->relativePath,
                        line: 1,
                        message: 'Legacy namespace imports are not allowed.',
                        suggestion: 'Migrate code out of App\\Legacy or add a layer_violation exception.',
                        ruleId: $this->id(),
                    ),
                ];
            }
        }

        return [];
    }
}
```

**src/AcmeGuardServiceProvider.php**

```php
namespace Acme\Guard;

use Acme\Guard\Rules\NoLegacyImportsRule;
use Illuminate\Support\ServiceProvider;
use LaravelGuard\Guard\Facades\Guard;

final class AcmeGuardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Guard::extend(NoLegacyImportsRule::class);
    }
}
```

Consumers install the package and run Guard — no config publish required unless they want to disable the rule.

## Related docs

- [rules.md](rules.md) — bundled rules and JSON output
- [configuration.md](configuration.md) — `rule_options` and severity
