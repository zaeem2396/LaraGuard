<?php

declare(strict_types=1);

use LaravelGuard\Guard\Support\JsonOutputSchema;

it('defines the stable json output schema version', function (): void {
    expect(JsonOutputSchema::VERSION)->toBe('1');
});
