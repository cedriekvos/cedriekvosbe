<?php

use App\About\Markdown\AboutFileParser;

covers(AboutFileParser::class);

it('parses YAML into a heading and bio map', function () {
    expect((new AboutFileParser)->parse("heading: About me\nbio: Hello"))
        ->toBe(['heading' => 'About me', 'bio' => 'Hello']);
});

it('returns an empty array for empty contents', function () {
    expect((new AboutFileParser)->parse(''))->toBe([]);
});

it('returns an empty array when the YAML is a scalar rather than a map', function () {
    expect((new AboutFileParser)->parse('just a scalar'))->toBe([]);
});
