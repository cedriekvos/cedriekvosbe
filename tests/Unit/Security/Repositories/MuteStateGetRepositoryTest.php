<?php

use App\Security\Json\MuteStateParser;
use App\Security\Repositories\MuteStateGetRepository;
use App\Security\Storage\MuteFileStorage;
use Illuminate\Support\Facades\Storage;

covers(MuteStateGetRepository::class);

beforeEach(function () {
    Storage::fake('security');
    $this->repository = new MuteStateGetRepository(new MuteFileStorage, new MuteStateParser);
});

it('reads and decodes the stored mute map', function () {
    Storage::disk('security')->put('vulnerability-mutes.json', '{"GHSA-aaaa|vendor/foo":"2026-06-09T12:00:00+00:00"}');

    expect($this->repository->get())->toBe(['GHSA-aaaa|vendor/foo' => '2026-06-09T12:00:00+00:00']);
});

it('returns an empty map when nothing is stored', function () {
    expect($this->repository->get())->toBe([]);
});
