<?php

use App\Blog\DraftSlug;
use App\Blog\PostPreviewFinder;
use Illuminate\Support\Facades\Storage;

covers(PostPreviewFinder::class);

beforeEach(function () {
    Storage::fake('posts');
    $this->finder = new PostPreviewFinder(postGetRepository(), new DraftSlug);
});

it('finds a published post by its slug', function () {
    writePostFile('welcome', 'T', '2026-05-01');

    expect($this->finder->find('welcome')->slug)->toBe('welcome');
});

it('finds a draft post by its clean slug', function () {
    writePostFile('draft-welcome', 'T', '2026-05-01');

    expect($this->finder->find('welcome')->slug)->toBe('draft-welcome');
});

it('finds a draft post by its stored slug', function () {
    writePostFile('draft-welcome', 'T', '2026-05-01');

    expect($this->finder->find('draft-welcome')->slug)->toBe('draft-welcome');
});

it('returns null when no post matches', function () {
    expect($this->finder->find('missing'))->toBeNull();
});
