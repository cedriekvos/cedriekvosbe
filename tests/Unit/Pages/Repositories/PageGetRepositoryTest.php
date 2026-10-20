<?php

use App\Pages\Repositories\PageGetRepository;
use Illuminate\Support\Facades\Storage;

covers(PageGetRepository::class);

beforeEach(function () {
    Storage::fake('pages');
    $this->repository = app(PageGetRepository::class);
});

it('excludes drafts and returns published pages sorted by title', function () {
    writePageFile('zebra', 'Zebra');
    writePageFile('draft-hidden', 'Hidden');
    writePageFile('apple', 'Apple');

    expect(array_column($this->repository->getAllExcludingDrafts(), 'slug'))->toBe(['apple', 'zebra']);
});

it('includes drafts and sorts them together with published pages', function () {
    writePageFile('zebra', 'Zebra');
    writePageFile('draft-apple', 'Apple');

    expect(array_column($this->repository->getAllIncludingDrafts(), 'slug'))->toBe(['draft-apple', 'zebra']);
});

it('tags pages whose slug starts with draft- as drafts', function () {
    writePageFile('draft-secret', 'Secret');
    writePageFile('public', 'Public');

    $bySlug = collect($this->repository->getAllIncludingDrafts())->keyBy('slug');

    expect($bySlug['draft-secret']->is_draft)->toBeTrue();
    expect($bySlug['public']->is_draft)->toBeFalse();
});

it('finds a single page by slug and tags its draft status', function () {
    writePageFile('draft-secret', 'Secret');

    $page = $this->repository->find('draft-secret');

    expect($page->title)->toBe('Secret');
    expect($page->is_draft)->toBeTrue();
});

it('returns null from find when the page is absent', function () {
    expect($this->repository->find('missing'))->toBeNull();
});
