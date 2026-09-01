<?php

use App\Security\Json\MuteStateSerializer;

covers(MuteStateSerializer::class);

beforeEach(function () {
    $this->serializer = new MuteStateSerializer;
});

it('encodes the mute map as json', function () {
    $json = $this->serializer->serialize(['GHSA-aaaa|vendor/foo' => '2026-06-09T12:00:00+00:00']);

    expect($json)->toBe('{"GHSA-aaaa|vendor\/foo":"2026-06-09T12:00:00+00:00"}');
});

it('encodes an empty map as an empty json array', function () {
    expect($this->serializer->serialize([]))->toBe('[]');
});
