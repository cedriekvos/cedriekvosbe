<?php

use App\Blog\Post;

covers(Post::class);

it('exposes every value passed to its constructor', function () {
    $post = new Post(
        slug: 'hello-world',
        title: 'Hello World',
        date: '2026-01-01',
        excerpt: 'A short intro',
        body: '# Hello',
        content: '<h1>Hello</h1>',
        read_time_minutes: 3,
        is_draft: true,
        is_featured: true,
    );

    expect($post->slug)->toBe('hello-world')
        ->and($post->title)->toBe('Hello World')
        ->and($post->date)->toBe('2026-01-01')
        ->and($post->excerpt)->toBe('A short intro')
        ->and($post->body)->toBe('# Hello')
        ->and($post->content)->toBe('<h1>Hello</h1>')
        ->and($post->read_time_minutes)->toBe(3)
        ->and($post->is_draft)->toBeTrue()
        ->and($post->is_featured)->toBeTrue();
});

it('defaults is_draft to false when omitted', function () {
    $post = new Post(
        slug: 'published',
        title: 'Published',
        date: '2026-01-01',
        excerpt: 'x',
        body: 'x',
        content: '<p>x</p>',
        read_time_minutes: 1,
    );

    expect($post->is_draft)->toBeFalse();
});

it('defaults is_featured to false when omitted', function () {
    $post = new Post(
        slug: 'published',
        title: 'Published',
        date: '2026-01-01',
        excerpt: 'x',
        body: 'x',
        content: '<p>x</p>',
        read_time_minutes: 1,
    );

    expect($post->is_featured)->toBeFalse();
});

it('can be constructed from a parsed post array via named-argument spreading', function () {
    $attributes = [
        'slug' => 'from-array',
        'title' => 'From Array',
        'date' => '2026-02-02',
        'excerpt' => 'spread',
        'body' => 'body',
        'content' => '<p>body</p>',
        'read_time_minutes' => 1,
        'is_draft' => false,
    ];

    $post = new Post(...$attributes);

    expect($post->slug)->toBe('from-array')
        ->and($post->is_draft)->toBeFalse();
});
