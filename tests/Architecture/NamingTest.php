<?php

/*
 * The roles a domain is built from, named by suffix so the module map can list them
 * (documentation/architecture/modules.html "Roles present").
 */

$repositories = [
    'App\About\Repositories',
    'App\Blog\Repositories',
    'App\Microblog\Repositories',
    'App\Scratchpad\Repositories',
    'App\Security\Repositories',
];

$storage = [
    'App\About\Storage',
    'App\Blog\Storage',
    'App\Microblog\Storage',
    'App\Scratchpad\Storage',
    'App\Security\Storage',
];

arch('repositories are suffixed Repository')
    ->expect($repositories)
    ->toHaveSuffix('Repository')
    // A source is the read side's own role — it reads raw records and hands the
    // repository DTOs — so it carries the Source suffix instead.
    ->ignoring([
        'App\Blog\Repositories\PostSource',
        'App\Microblog\Repositories\MessageSource',
        'App\Security\Repositories\VulnerabilitySource',
    ]);

arch('file storage is suffixed FileStorage')
    ->expect($storage)
    ->toHaveSuffix('FileStorage');

arch('storage is only reached through a repository or a source')
    ->expect($storage)
    ->toOnlyBeUsedIn($repositories);
