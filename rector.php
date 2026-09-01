<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Transform\Rector\String_\StringToClassConstantRector;
use RectorLaravel\Set\LaravelLevelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
    ])
    ->withSkip([
        __DIR__.'/app/Http/Controllers/Controller.php',
        __DIR__.'/app/Models/User.php',
        __DIR__.'/app/Providers/AppServiceProvider.php',
        // 'auth.login' is mapped to Illuminate\Auth\Events\Login::class by the
        // Laravel 5.2 set — but here it is a Blade view name, not an event name.
        StringToClassConstantRector::class => [
            __DIR__.'/app/Http/Controllers/Auth/AuthenticatedSessionController.php',
        ],
    ])
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
    )
    ->withSets([
        LaravelLevelSetList::UP_TO_LARAVEL_130,
    ]);
