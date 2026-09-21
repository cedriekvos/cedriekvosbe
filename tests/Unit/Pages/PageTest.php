<?php

use App\Pages\Page;

covers(Page::class);

it('exposes every value passed to its constructor', function () {
    $page = new Page(
        slug: 'about',
        title: 'About',
        body: '# About',
        content: '<h1>About</h1>',
        is_draft: true,
    );

    expect($page->slug)->toBe('about')
        ->and($page->title)->toBe('About')
        ->and($page->body)->toBe('# About')
        ->and($page->content)->toBe('<h1>About</h1>')
        ->and($page->is_draft)->toBeTrue();
});

it('defaults is_draft to false when omitted', function () {
    $page = new Page(
        slug: 'published',
        title: 'Published',
        body: 'x',
        content: '<p>x</p>',
    );

    expect($page->is_draft)->toBeFalse();
});

it('can be constructed from a parsed page array via named-argument spreading', function () {
    $attributes = [
        'slug' => 'from-array',
        'title' => 'From Array',
        'body' => 'body',
        'content' => '<p>body</p>',
        'is_draft' => false,
    ];

    $page = new Page(...$attributes);

    expect($page->slug)->toBe('from-array')
        ->and($page->is_draft)->toBeFalse();
});
