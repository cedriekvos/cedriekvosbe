<?php

use App\Markdown\FrontMatterParser;
use App\Microblog\Markdown\MessageFileParser;
use App\Microblog\MessageSorter;
use App\Microblog\Repositories\MessageGetRepository;
use App\Microblog\Repositories\MessageSource;
use App\Microblog\Storage\MessageFileStorage;
use Illuminate\Support\Facades\Storage;

covers(MessageGetRepository::class);

beforeEach(function () {
    Storage::fake('microblog');
    $this->repository = new MessageGetRepository(
        new MessageSource(new MessageFileStorage, new MessageFileParser(new FrontMatterParser), messageFactory()),
        new MessageSorter,
    );
});

it('returns every message newest first', function () {
    writeMessageFile('old', 'Old', '2026-01-01 00:00:00');
    writeMessageFile('new', 'New', '2026-06-01 00:00:00');

    expect(array_column($this->repository->getAllSortedByPostedAtDescending(), 'id'))->toBe(['new', 'old']);
});

it('finds a single message by id', function () {
    writeMessageFile('hello', 'Hello', '2026-01-01 00:00:00');

    expect($this->repository->find('hello')->body)->toBe('Hello');
});

it('returns null from find when the message is absent', function () {
    expect($this->repository->find('missing'))->toBeNull();
});

it('reports existence by id', function () {
    writeMessageFile('hello', 'Hello', '2026-01-01 00:00:00');

    expect($this->repository->exists('hello'))->toBeTrue();
    expect($this->repository->exists('missing'))->toBeFalse();
});
