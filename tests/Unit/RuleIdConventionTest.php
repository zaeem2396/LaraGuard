<?php

declare(strict_types=1);

use LaravelGuard\Guard\Support\RuleIdConvention;

it('derives kebab-case rule ids from class basenames', function (): void {
    expect(RuleIdConvention::fromClass('App\\Rules\\ForbiddenWordRule'))->toBe('forbidden-word');
    expect(RuleIdConvention::fromClass('Acme\\Guard\\AcmeLayerRule'))->toBe('acme-layer');
    expect(RuleIdConvention::fromClass('Vendor\\NoSuffix'))->toBe('no-suffix');
});
