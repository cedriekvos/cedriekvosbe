<?php

use App\Markdown\FrontMatterParser;
use App\Microblog\Markdown\MessageFileParser;
use App\Microblog\Repositories\MessageSource;
use App\Microblog\Storage\MessageFileStorage;
use Illuminate\Support\Facades\Storage;

covers(MessageSource::class);

beforeEach(function () {
    Storage::fake('microblog');
    $this->source = new MessageSource(new MessageFileStorage, new MessageFileParser(new FrontMatterParser), messageFactory());
});

it('builds a message from each file', function () {
    writeMessageFile('one', 'First', '2026-01-01 00:00:00');
    writeMessageFile('two', 'Second', '2026-01-02 00:00:00');

    $byId = collect($this->source->all())->keyBy('id');

    expect($byId)->toHaveCount(2);
    expect($byId['one']->body)->toBe('First');
});

it('returns an empty array when there are no messages', function () {
    expect($this->source->all())->toBe([]);
});

it('finds a single message by id', function () {
    writeMessageFile('secret', 'Secret', '2026-01-01 00:00:00');

    $message = $this->source->find('secret');

    expect($message->body)->toBe('Secret');
});

it('returns null from find when the message is absent', function () {
    expect($this->source->find('missing'))->toBeNull();
});

it('reports existence by id', function () {
    writeMessageFile('hello', 'Hello', '2026-01-01 00:00:00');

    expect($this->source->exists('hello'))->toBeTrue();
    expect($this->source->exists('missing'))->toBeFalse();
});
