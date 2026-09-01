<?php

use App\Security\Json\MuteStateParser;

covers(MuteStateParser::class);

beforeEach(function () {
    $this->parser = new MuteStateParser;
});

it('decodes a stored mute map', function () {
    $state = $this->parser->parse('{"GHSA-aaaa|vendor/foo":"2026-06-09T12:00:00+00:00"}');

    expect($state)->toBe(['GHSA-aaaa|vendor/foo' => '2026-06-09T12:00:00+00:00']);
});

it('returns an empty map for empty or invalid content', function () {
    expect($this->parser->parse(''))->toBe([])
        ->and($this->parser->parse('not json'))->toBe([]);
});

it('skips entries whose key or value is not a string', function () {
    expect($this->parser->parse('{"valid":"2026","skip":123}'))->toBe(['valid' => '2026'])
        ->and($this->parser->parse('["zero-indexed"]'))->toBe([]);
});
