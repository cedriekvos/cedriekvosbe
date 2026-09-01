<?php

use App\Blog\DraftSlug;
use App\Blog\PostFilter;

covers(PostFilter::class);

beforeEach(function () {
    $this->filter = new PostFilter(new DraftSlug);
});

it('removes draft slugs and keeps published ones', function () {
    expect($this->filter->excludeDrafts(['published', 'draft-wip']))->toBe(['published']);
});

it('preserves the order of the remaining slugs', function () {
    expect($this->filter->excludeDrafts(['first', 'draft-wip', 'second']))->toBe(['first', 'second']);
});

it('reindexes the array after removing drafts', function () {
    expect(array_keys($this->filter->excludeDrafts(['first', 'draft-wip', 'second'])))->toBe([0, 1]);
});

it('returns an empty array unchanged', function () {
    expect($this->filter->excludeDrafts([]))->toBe([]);
});

it('keeps a slug that only contains the prefix later on', function () {
    expect($this->filter->excludeDrafts(['notes-draft-one']))->toBe(['notes-draft-one']);
});
