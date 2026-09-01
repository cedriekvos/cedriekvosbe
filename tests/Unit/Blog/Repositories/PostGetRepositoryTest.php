<?php

use App\Blog\Repositories\PostGetRepository;
use Illuminate\Support\Facades\Storage;

covers(PostGetRepository::class);

beforeEach(function () {
    Storage::fake('posts');
    $this->repository = postGetRepository();
});

it('excludes drafts and returns published posts newest first', function () {
    writePostFile('old', 'Old', '2026-01-01');
    writePostFile('draft-hidden', 'Hidden', '2026-12-01');
    writePostFile('new', 'New', '2026-06-01');

    expect(array_column($this->repository->getAllExcludingDrafts(), 'slug'))->toBe(['new', 'old']);
});

it('includes drafts and sorts them together newest first', function () {
    writePostFile('pub', 'Published', '2026-01-01');
    writePostFile('draft-drafty', 'Drafty', '2026-12-01');

    expect(array_column($this->repository->getAllIncludingDrafts(), 'slug'))->toBe(['draft-drafty', 'pub']);
});

it('breaks date ties alphabetically by title', function () {
    writePostFile('z', 'Zebra', '2026-05-10');
    writePostFile('a', 'Apple', '2026-05-10');

    expect(array_column($this->repository->getAllExcludingDrafts(), 'title'))->toBe(['Apple', 'Zebra']);
});

it('tags posts whose slug starts with draft- as drafts', function () {
    writePostFile('draft-secret', 'Secret', '2026-01-01');
    writePostFile('public', 'Public', '2026-01-01');

    $bySlug = collect($this->repository->getAllIncludingDrafts())->keyBy('slug');

    expect($bySlug['draft-secret']->is_draft)->toBeTrue();
    expect($bySlug['public']->is_draft)->toBeFalse();
});

it('finds a single post by slug and tags its draft status', function () {
    writePostFile('draft-secret', 'Secret', '2026-01-01');

    $post = $this->repository->find('draft-secret');

    expect($post->title)->toBe('Secret');
    expect($post->is_draft)->toBeTrue();
});

it('returns null from find when the post is absent', function () {
    expect($this->repository->find('missing'))->toBeNull();
});

it('reports existence by slug', function () {
    writePostFile('hello', 'Hello', '2026-01-01');

    expect($this->repository->exists('hello'))->toBeTrue();
    expect($this->repository->exists('missing'))->toBeFalse();
});
