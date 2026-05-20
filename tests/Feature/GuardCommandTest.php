<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

it('runs the guard command with a successful exit code', function (): void {
    expect(Artisan::call('guard', [
        '--format' => 'txt',
    ]))->toBe(0);
});

it('emits valid json when requested', function (): void {
    Artisan::call('guard', [
        '--format' => 'json',
    ]);

    $decoded = json_decode(trim((string) Artisan::output()), true, 512, JSON_THROW_ON_ERROR);

    expect($decoded)->toHaveKeys(['errors', 'warnings', 'info', 'summary']);
});

it('can fail when architectural errors are present and the flag is provided', function (): void {
    expect(Artisan::call('guard', [
        '--format' => 'json',
        '--fail-on-error' => true,
    ]))->toBe(0);
});
