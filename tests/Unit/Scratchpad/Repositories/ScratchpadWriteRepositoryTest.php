<?php

use App\Scratchpad\Repositories\ScratchpadWriteRepository;
use App\Scratchpad\Storage\ScratchpadFileStorage;
use Illuminate\Support\Facades\Storage;

covers(ScratchpadWriteRepository::class);

beforeEach(function () {
    Storage::fake('meta');
    $this->repository = new ScratchpadWriteRepository(new ScratchpadFileStorage);
});

it('writes the content to storage', function () {
    $this->repository->save('Remember to check backlinks.');

    expect(Storage::disk('meta')->get('scratchpad.md'))->toBe('Remember to check backlinks.');
});
