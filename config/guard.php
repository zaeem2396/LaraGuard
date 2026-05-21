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
    | Disabled rules are skipped entirely during analysis.
    |
    */
    'rules' => [
        NoDbInControllerRule::class,
        FatClassRule::class,
        MissingInterfaceBindingRule::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored paths
    |--------------------------------------------------------------------------
    |
    | Absolute or project-relative path prefixes skipped by the scanner.
    | Glob-style patterns can be added in future releases; prefix match is used
    | by the default scanner for predictable zero-config behavior.
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
    | Minimum severity emitted as console "errors"
    |--------------------------------------------------------------------------
    |
    | error | warning | info — controls grouping in human output and exit logic.
    |
    */
    'severity' => [
        'report_from' => 'info',
        'fail_on' => 'error',
    ],

    /*
    |--------------------------------------------------------------------------
    | Scan roots (relative to application base path)
    |--------------------------------------------------------------------------
    */
    'paths' => [
        'app',
    ],
];
