<?php

use App\Pages\PageSorter;

covers(PageSorter::class);

beforeEach(function () {
    $this->sorter = new PageSorter;
});

it('orders pages alphabetically by title', function () {
    $sorted = $this->sorter->sortByTitle([
        makePage(slug: 'zebra', title: 'Zebra'),
        makePage(slug: 'apple', title: 'Apple'),
        makePage(slug: 'mango', title: 'Mango'),
    ]);

    expect(array_column($sorted, 'slug'))->toBe(['apple', 'mango', 'zebra']);
});

it('returns an empty array unchanged', function () {
    expect($this->sorter->sortByTitle([]))->toBe([]);
});
