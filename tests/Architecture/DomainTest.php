<?php

/*
 * The bounded domains and the shared kernel (documentation/architecture/modules.html):
 * self-contained, file-backed, and unaware of HTTP.
 */

$domains = [
    'App\About',
    'App\Blog',
    'App\Markdown',
    'App\Microblog',
    'App\Scratchpad',
    'App\Security',
];

$delivery = [
    'App\Console',
    'App\Http',
    'App\Livewire',
    'App\Models',
    'App\Providers',
    'App\View',
];

/*
 * What each domain may reach for besides itself. App\Markdown is the shared kernel,
 * so every domain may use it. App\About is the one exception the module map records:
 * AboutFactory renders bios with App\Blog\Markdown\PostMarkdownToHtmlConverter.
 */
$allowed = [
    'App\About' => ['App\Markdown', 'App\Blog'],
    'App\Blog' => ['App\Markdown'],
    'App\Markdown' => [],
    'App\Microblog' => ['App\Markdown'],
    'App\Scratchpad' => [],
    'App\Security' => [],
];

arch('domain classes are final, readonly and strictly typed')
    ->expect($domains)
    ->toBeFinal()
    ->toBeReadonly()
    ->toUseStrictTypes();

arch('domains do not know about the delivery layer')
    ->expect($domains)
    // App\Mail is deliberately absent from this list: App\Security\VulnerabilityNotifier
    // sends App\Mail\ComposerVulnerabilityAlert, the one upward crossing the module map
    // records. Every other delivery module stays out of the domains.
    ->not->toUse($delivery);

arch('domains do not know about requests, responses or the database')
    ->expect($domains)
    ->not->toUse([
        'Illuminate\Http',
        'Illuminate\Database',
        'Illuminate\Routing',
        'Illuminate\View',
        'Livewire\Component',
    ]);

foreach ($domains as $domain) {
    $forbidden = array_values(array_diff($domains, [$domain], $allowed[$domain]));

    arch($domain.' does not reach into another domain')
        ->expect($domain)
        ->not->toUse($forbidden);
}

arch('only storage classes touch the filesystem')
    ->expect('Illuminate\Support\Facades\Storage')
    ->toOnlyBeUsedIn([
        'App\About\Storage',
        'App\Blog\Storage',
        'App\Microblog\Storage',
        'App\Scratchpad\Storage',
        'App\Security\Storage',
    ]);
