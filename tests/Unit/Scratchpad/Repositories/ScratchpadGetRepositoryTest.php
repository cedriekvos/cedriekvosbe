<?php

use App\Scratchpad\Repositories\ScratchpadGetRepository;
use App\Scratchpad\Scratchpad;
use App\Scratchpad\Storage\ScratchpadFileStorage;
use Illuminate\Support\Facades\Storage;

covers(ScratchpadGetRepository::class);

beforeEach(function () {
    Storage::fake('meta');
    $this->repository = new ScratchpadGetRepository(new ScratchpadFileStorage);
});

it('builds the scratchpad from the stored file', function () {
    Storage::disk('meta')->put('scratchpad.md', 'Remember to check backlinks.');

    $scratchpad = $this->repository->get();

    expect($scratchpad)->toBeInstanceOf(Scratchpad::class)
        ->and($scratchpad->content)->toBe('Remember to check backlinks.');
});

it('returns an empty scratchpad when nothing is stored', function () {
    $scratchpad = $this->repository->get();

    expect($scratchpad->content)->toBe('');
});
