<?php

declare(strict_types=1);

use LaravelGuard\Guard\Rules\FatClassRule;
use LaravelGuard\Guard\Rules\MissingInterfaceBindingRule;
use LaravelGuard\Guard\Rules\NoDbInControllerRule;

return [
    /*
    |--------------------------------------------------------------------------
    | Enabled rules
    |--------------------------------------------------------------------------
    |
    | Fully-qualified class names implementing LaravelGuard\Guard\Contracts\RuleContract.
    | Use rule_options.{rule-id}.enabled to disable a rule without removing it.
    |
    */
    'rules' => [
        NoDbInControllerRule::class,
        FatClassRule::class,
        MissingInterfaceBindingRule::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-rule options
    |--------------------------------------------------------------------------
    |
    | Keyed by rule id (e.g. no-db-in-controller). Set enabled => false to skip
    | a rule. Optionally override emitted violation severity per rule.
    |
    */
    'rule_options' => [
        'no-db-in-controller' => [
            'enabled' => true,
            'severity' => 'error',
        ],
        'fat-class' => [
            'enabled' => true,
            'severity' => 'info',
        ],
        'missing-interface-binding' => [
            'enabled' => true,
            'severity' => 'info',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored paths
    |--------------------------------------------------------------------------
    |
    | Project-relative path prefixes skipped by the scanner. These merge with
    | patterns from a root `.guardignore` file (publish with --tag=guard-ignore).
    | Use glob characters (*, ?, **) and negation (!path) in `.guardignore`.
    |
    */
    'ignore' => [
        'bootstrap/cache',
        'storage',
        'vendor',
        'tests',
    ],

    /*
    |--------------------------------------------------------------------------
    | Thresholds
    |--------------------------------------------------------------------------
    */
    'thresholds' => [
        'fat_class' => [
            'max_method_count' => 20,
            'max_public_method_count' => 12,
            // Inclusive line span of each class body; set to 0 to disable.
            'max_line_count' => 200,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | no-db-in-controller options
    |--------------------------------------------------------------------------
    |
    | Path prefixes (relative to the app root, forward slashes) skipped by
    | the no-db-in-controller rule even when under Http/Controllers.
    |
    */
    'no_db_in_controller' => [
        'exclude_path_prefixes' => [
            // 'app/Http/Controllers/Api/V1',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Service provider binding scan
    |--------------------------------------------------------------------------
    |
    | Paths scanned to build an interface => concrete map for
    | missing-interface-binding (bind, singleton, scoped calls).
    |
    */
    'provider_bindings' => [
        'scan_paths' => [
            'app/Providers',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | missing-interface-binding options
    |--------------------------------------------------------------------------
    |
    | strict: when true, flag constructors that type-hint a concrete class
    | even when the container already binds an interface to that concrete.
    |
    */
    'missing_interface_binding' => [
        'strict' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Minimum severity emitted as console "errors"
    |--------------------------------------------------------------------------
    |
    | error | warning | info — controls grouping in human output and exit logic.
    | Override with GUARD_FAIL_ON in your .env when published.
    |
    */
    'severity' => [
        'report_from' => 'info',
        'fail_on' => env('GUARD_FAIL_ON', 'error'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Scan roots (relative to application base path)
    |--------------------------------------------------------------------------
    |
    | Override with comma-separated GUARD_PATHS in your .env when published.
    | Must contain at least one non-empty path.
    |
    */
    'paths' => array_values(array_filter(array_map(
        static fn (string $path): string => trim($path),
        explode(',', (string) env('GUARD_PATHS', 'app')),
    ))),
];
