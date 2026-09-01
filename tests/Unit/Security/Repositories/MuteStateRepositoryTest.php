<?php

use App\Security\Repositories\MuteStateRepository;
use Illuminate\Support\Facades\Storage;

covers(MuteStateRepository::class);

beforeEach(function () {
    Storage::fake('security');
    $this->repository = app(MuteStateRepository::class);
});

it('saves a mute map and reads it back through the facade', function () {
    $this->repository->save(['GHSA-aaaa|vendor/foo' => '2026-06-09T12:00:00+00:00']);

    expect($this->repository->get())->toBe(['GHSA-aaaa|vendor/foo' => '2026-06-09T12:00:00+00:00']);
});

it('returns an empty map before anything is saved', function () {
    expect($this->repository->get())->toBe([]);
});
