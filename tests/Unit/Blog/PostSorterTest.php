<?php

use App\Blog\PostSorter;

covers(PostSorter::class);

beforeEach(function () {
    $this->sorter = new PostSorter;
});

it('orders posts newest first by date', function () {
    $sorted = $this->sorter->sortByDateDescending([
        makePost(slug: 'old', title: 'Old', date: '2026-01-01'),
        makePost(slug: 'new', title: 'New', date: '2026-09-01'),
        makePost(slug: 'mid', title: 'Mid', date: '2026-05-01'),
    ]);

    expect(array_column($sorted, 'slug'))->toBe(['new', 'mid', 'old']);
});

it('breaks date ties alphabetically by title', function () {
    $sorted = $this->sorter->sortByDateDescending([
        makePost(slug: 'z', title: 'Zebra', date: '2026-05-10'),
        makePost(slug: 'a', title: 'Apple', date: '2026-05-10'),
    ]);

    expect(array_column($sorted, 'title'))->toBe(['Apple', 'Zebra']);
});

it('treats an empty title as sorting before titled same-date posts', function () {
    $sorted = $this->sorter->sortByDateDescending([
        makePost(slug: 'titled', title: 'Apple', date: '2026-05-10'),
        makePost(slug: 'untitled', title: '', date: '2026-05-10'),
    ]);

    expect(array_column($sorted, 'slug'))->toBe(['untitled', 'titled']);
});

it('sorts posts with unparseable dates after valid-date posts', function () {
    $sorted = $this->sorter->sortByDateDescending([
        makePost(slug: 'bad', title: 'Bad', date: 'not-a-date'),
        makePost(slug: 'valid', title: 'Valid', date: '2026-01-01'),
    ]);

    expect(array_column($sorted, 'slug'))->toBe(['valid', 'bad']);
});

it('breaks ties between unparseable-date posts alphabetically by title', function () {
    $sorted = $this->sorter->sortByDateDescending([
        makePost(slug: 'z', title: 'Zebra', date: 'not-a-date'),
        makePost(slug: 'a', title: 'Apple', date: 'not-a-date'),
    ]);

    expect(array_column($sorted, 'slug'))->toBe(['a', 'z']);
});

it('breaks ties between unparseable-date posts the same way regardless of input order', function () {
    $sorted = $this->sorter->sortByDateDescending([
        makePost(slug: 'a', title: 'Apple', date: 'not-a-date'),
        makePost(slug: 'z', title: 'Zebra', date: 'not-a-date'),
    ]);

    expect(array_column($sorted, 'slug'))->toBe(['a', 'z']);
});

it('returns an empty array unchanged', function () {
    expect($this->sorter->sortByDateDescending([]))->toBe([]);
});
