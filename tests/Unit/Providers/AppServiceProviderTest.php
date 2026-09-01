<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\DevCommands;

/*
 * The dev processes are registered by the framework only when the "dev" command
 * itself runs, so the baseline is registered here to give the filter something
 * to act on.
 */
beforeEach(function (): void {
    DevCommands::artisan('serve', 'server');
    DevCommands::artisan('queue:listen --tries=1 --timeout=0', 'queue');
});

afterEach(function (): void {
    DevCommands::except();
});

it('drops the redundant dev server process locally', function (): void {
    app()->detectEnvironment(fn (): string => 'local');

    (new AppServiceProvider(app()))->boot();

    expect(array_column(DevCommands::commands(), 'name'))
        ->not->toContain('server')
        ->toContain('queue');
});

it('keeps the dev server process outside local', function (): void {
    app()->detectEnvironment(fn (): string => 'production');

    (new AppServiceProvider(app()))->boot();

    expect(array_column(DevCommands::commands(), 'name'))
        ->toContain('server')
        ->toContain('queue');
});
