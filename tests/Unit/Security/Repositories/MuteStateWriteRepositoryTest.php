<?php

use App\Security\Json\MuteStateSerializer;
use App\Security\Repositories\MuteStateWriteRepository;
use App\Security\Storage\MuteFileStorage;
use Illuminate\Support\Facades\Storage;

covers(MuteStateWriteRepository::class);

beforeEach(function () {
    Storage::fake('security');
    $this->repository = new MuteStateWriteRepository(new MuteFileStorage, new MuteStateSerializer);
});

it('serializes and writes the mute map to storage', function () {
    $this->repository->save(['GHSA-aaaa|vendor/foo' => '2026-06-09T12:00:00+00:00']);

    expect(Storage::disk('security')->get('vulnerability-mutes.json'))
        ->toBe('{"GHSA-aaaa|vendor\/foo":"2026-06-09T12:00:00+00:00"}');
});
