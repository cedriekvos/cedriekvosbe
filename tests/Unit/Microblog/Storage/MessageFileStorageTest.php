<?php

use App\Microblog\Storage\MessageFileStorage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

covers(MessageFileStorage::class);

beforeEach(function () {
    Storage::fake('microblog');
    $this->storage = new MessageFileStorage;
});

it('returns an empty array when there are no messages', function () {
    expect($this->storage->all())->toBe([]);
});

it('returns the id of every markdown message', function () {
    Storage::disk('microblog')->put('one.md', 'body');
    Storage::disk('microblog')->put('two.md', 'body');

    expect(collect($this->storage->all())->sort()->values()->all())
        ->toBe(['one', 'two']);
});

it('ignores non-markdown files', function () {
    Storage::disk('microblog')->put('notes.txt', 'not a message');
    Storage::disk('microblog')->put('message.md', 'body');

    expect($this->storage->all())->toBe(['message']);
});

it('reads the contents of a message by id', function () {
    Storage::disk('microblog')->put('hello.md', 'the body');

    expect($this->storage->read('hello'))->toBe('the body');
});

it('returns an empty string when reading an id that does not exist', function () {
    expect($this->storage->read('missing'))->toBe('');
});

it('reports existence by id', function () {
    Storage::disk('microblog')->put('here.md', 'body');

    expect($this->storage->exists('here'))->toBeTrue();
    expect($this->storage->exists('nope'))->toBeFalse();
});

it('writes a message by id', function () {
    $this->storage->put('written', 'contents');

    expect(Storage::disk('microblog')->get('written.md'))->toBe('contents');
});

it('deletes a message by id', function () {
    Storage::disk('microblog')->put('doomed.md', 'body');

    expect($this->storage->delete('doomed'))->toBeTrue();
    expect(Storage::disk('microblog')->exists('doomed.md'))->toBeFalse();
});

it('returns false when deleting a message that does not exist', function () {
    expect($this->storage->delete('ghost'))->toBeFalse();
});

it('generates a ULID as a new message id', function () {
    expect(Str::isUlid($this->storage->generateId()))->toBeTrue();
});

it('generates a different id on each call', function () {
    expect($this->storage->generateId())->not->toBe($this->storage->generateId());
});
