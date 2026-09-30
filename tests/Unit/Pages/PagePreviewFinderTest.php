<?php

use App\Pages\DraftSlug;
use App\Pages\PagePreviewFinder;
use Illuminate\Support\Facades\Storage;

covers(PagePreviewFinder::class);

beforeEach(function () {
    Storage::fake('pages');
    $this->finder = new PagePreviewFinder(pageGetRepository(), new DraftSlug);
});

it('finds a published page by its slug', function () {
    writePageFile('welcome', 'T');

    expect($this->finder->find('welcome')->slug)->toBe('welcome');
});

it('finds a draft page by its clean slug', function () {
    writePageFile('draft-welcome', 'T');

    expect($this->finder->find('welcome')->slug)->toBe('draft-welcome');
});

it('finds a draft page by its stored slug', function () {
    writePageFile('draft-welcome', 'T');

    expect($this->finder->find('draft-welcome')->slug)->toBe('draft-welcome');
});

it('returns null when no page matches', function () {
    expect($this->finder->find('missing'))->toBeNull();
});
