<?php

use App\Blog\Post;
use App\Blog\PostFactory;

covers(PostFactory::class);

beforeEach(function () {
    $this->factory = new PostFactory;
});

it('builds a post from parsed data', function () {
    $post = $this->factory->make([
        'slug' => 'hello-world',
        'title' => 'Hello World',
        'date' => '2026-01-01',
        'excerpt' => 'An excerpt',
        'body' => 'The body',
        'content' => '<p>The body</p>',
        'read_time_minutes' => 3,
    ], isDraft: false);

    expect($post)->toBeInstanceOf(Post::class)
        ->and($post->slug)->toBe('hello-world')
        ->and($post->title)->toBe('Hello World')
        ->and($post->date)->toBe('2026-01-01')
        ->and($post->excerpt)->toBe('An excerpt')
        ->and($post->body)->toBe('The body')
        ->and($post->content)->toBe('<p>The body</p>')
        ->and($post->read_time_minutes)->toBe(3)
        ->and($post->is_draft)->toBeFalse();
});

it('marks the post as a draft when told to', function () {
    $post = $this->factory->make(['slug' => 'wip'], isDraft: true);

    expect($post->is_draft)->toBeTrue();
});

it('marks the post as featured when the frontmatter says so', function () {
    $post = $this->factory->make(['slug' => 'featured', 'featured' => true], isDraft: false);

    expect($post->is_featured)->toBeTrue();
});

it('defaults is_featured to false when missing or not a bool', function () {
    $missing = $this->factory->make(['slug' => 'no-featured-key'], isDraft: false);
    $wrongType = $this->factory->make(['slug' => 'wrong-type', 'featured' => 'yes'], isDraft: false);

    expect($missing->is_featured)->toBeFalse()
        ->and($wrongType->is_featured)->toBeFalse();
});

it('defaults missing or non-string fields to empty strings', function () {
    $post = $this->factory->make([
        'slug' => 'partial',
        'title' => 123,
    ], isDraft: false);

    expect($post->slug)->toBe('partial')
        ->and($post->title)->toBe('')
        ->and($post->date)->toBe('')
        ->and($post->excerpt)->toBe('')
        ->and($post->body)->toBe('')
        ->and($post->content)->toBe('')
        ->and($post->read_time_minutes)->toBe(1);
});
