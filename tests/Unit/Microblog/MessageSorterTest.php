<?php

use App\Microblog\MessageSorter;

covers(MessageSorter::class);

beforeEach(function () {
    $this->sorter = new MessageSorter;
});

it('orders messages newest first by posted_at', function () {
    $sorted = $this->sorter->sortByPostedAtDescending([
        makeMessage(id: 'old', postedAt: '2026-01-01 00:00:00'),
        makeMessage(id: 'new', postedAt: '2026-09-01 00:00:00'),
        makeMessage(id: 'mid', postedAt: '2026-05-01 00:00:00'),
    ]);

    expect(array_column($sorted, 'id'))->toBe(['new', 'mid', 'old']);
});

it('breaks posted_at ties by id descending', function () {
    $sorted = $this->sorter->sortByPostedAtDescending([
        makeMessage(id: '01AAA', postedAt: '2026-05-10 00:00:00'),
        makeMessage(id: '01BBB', postedAt: '2026-05-10 00:00:00'),
    ]);

    expect(array_column($sorted, 'id'))->toBe(['01BBB', '01AAA']);
});

it('sorts messages with unparseable posted_at after valid ones', function () {
    $sorted = $this->sorter->sortByPostedAtDescending([
        makeMessage(id: 'bad', postedAt: 'not-a-date'),
        makeMessage(id: 'valid', postedAt: '2026-01-01 00:00:00'),
    ]);

    expect(array_column($sorted, 'id'))->toBe(['valid', 'bad']);
});

it('breaks ties between unparseable-posted_at messages deterministically by id', function () {
    $sorted = $this->sorter->sortByPostedAtDescending([
        makeMessage(id: '01AAA', postedAt: 'not-a-date'),
        makeMessage(id: '01BBB', postedAt: 'not-a-date'),
    ]);

    expect(array_column($sorted, 'id'))->toBe(['01BBB', '01AAA']);
});

it('keeps the higher id first when unparseable-posted_at messages are already in descending id order', function () {
    $sorted = $this->sorter->sortByPostedAtDescending([
        makeMessage(id: '01BBB', postedAt: 'not-a-date'),
        makeMessage(id: '01AAA', postedAt: 'not-a-date'),
    ]);

    expect(array_column($sorted, 'id'))->toBe(['01BBB', '01AAA']);
});

it('returns an empty array unchanged', function () {
    expect($this->sorter->sortByPostedAtDescending([]))->toBe([]);
});
