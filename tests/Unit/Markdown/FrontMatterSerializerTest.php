<?php

use App\Markdown\FrontMatterSerializer;

covers(FrontMatterSerializer::class);

beforeEach(function () {
    $this->serializer = new FrontMatterSerializer;
});

it('wraps the front matter in a fence and separates the body with a single blank line', function () {
    $output = $this->serializer->serialize(['title' => 'Hello'], 'Body content');

    expect($output)->toBe("---\ntitle: Hello\n---\n\nBody content\n");
});

it('dumps every front matter entry', function () {
    $output = $this->serializer->serialize(['title' => 'Hello', 'date' => '2026-01-01'], 'Body');

    expect($output)->toBe("---\ntitle: Hello\ndate: '2026-01-01'\n---\n\nBody\n");
});

it('normalizes crlf line endings in the body', function () {
    $output = $this->serializer->serialize(['title' => 'Hello'], "\r\n# Body\r\nContent\r\n");

    expect($output)
        ->toContain("---\n\n# Body\nContent\n")
        ->not->toContain("\r");
});

it('trims surrounding blank lines from the body so repeated saves stay byte-stable', function () {
    $output = $this->serializer->serialize(['title' => 'Hello'], "\n\n# Heading\nContent\n\n");

    expect($output)
        ->toContain("---\n\n# Heading\nContent\n")
        ->toEndWith("Content\n")
        ->not->toContain("Content\n\n");
});

it('keeps blank lines inside the body', function () {
    $output = $this->serializer->serialize(['title' => 'Hello'], "First\n\nSecond");

    expect($output)->toEndWith("---\n\nFirst\n\nSecond\n");
});
