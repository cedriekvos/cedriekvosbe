<?php

use App\Scratchpad\Repositories\ScratchpadGetRepository;
use App\Scratchpad\Repositories\ScratchpadRepository;
use App\Scratchpad\Repositories\ScratchpadWriteRepository;
use App\Scratchpad\Scratchpad;
use App\Scratchpad\Storage\ScratchpadFileStorage;
use Illuminate\Support\Facades\Storage;

covers(ScratchpadRepository::class);

beforeEach(function () {
    Storage::fake('meta');

    $storage = new ScratchpadFileStorage;
    $this->repository = new ScratchpadRepository(
        new ScratchpadGetRepository($storage),
        new ScratchpadWriteRepository($storage),
    );
});

it('saves the scratchpad and reads it back through the facade', function () {
    $this->repository->save('Remember to check backlinks.');

    $scratchpad = $this->repository->get();

    expect($scratchpad)->toBeInstanceOf(Scratchpad::class)
        ->and($scratchpad->content)->toBe('Remember to check backlinks.');
});
