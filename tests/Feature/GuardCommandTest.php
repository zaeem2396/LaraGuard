<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

it('runs the guard command with a successful exit code when not failing on errors', function (): void {
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
    expect($decoded['summary'])->toHaveKey('files_scanned');
    expect($decoded['summary']['files_scanned'])->toBeGreaterThan(0);
    expect($decoded['errors'])->not->toBeEmpty();
});

it('prints scanned file count when stats is enabled', function (): void {
    Artisan::call('guard', [
        '--format' => 'txt',
        '--stats' => true,
    ]);

    expect(Artisan::output())->toContain('Scanned');
});

it('fails with a non-zero exit code when errors exist and fail-on-error is enabled', function (): void {
    expect(Artisan::call('guard', [
        '--format' => 'json',
        '--fail-on-error' => true,
    ]))->toBe(1);
});
