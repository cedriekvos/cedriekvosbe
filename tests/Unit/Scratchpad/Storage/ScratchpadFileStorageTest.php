<?php

use App\Scratchpad\Storage\ScratchpadFileStorage;
use Illuminate\Support\Facades\Storage;

covers(ScratchpadFileStorage::class);

beforeEach(function () {
    Storage::fake('meta');
    $this->storage = new ScratchpadFileStorage;
});

it('returns an empty string when nothing has been written', function () {
    expect($this->storage->read())->toBe('');
});

it('writes contents and reads them back', function () {
    $this->storage->write('Remember to check backlinks.');

    expect($this->storage->read())->toBe('Remember to check backlinks.');
});

it('stores the file on the shared meta disk, separate from posts', function () {
    $this->storage->write('Remember to check backlinks.');

    expect(Storage::disk('meta')->exists('scratchpad.md'))->toBeTrue();
});
