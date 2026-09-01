<?php

use App\Markdown\FrontMatterSerializer;
use App\Microblog\Markdown\MessageFileSerializer;

covers(MessageFileSerializer::class);

beforeEach(function () {
    $this->serializer = new MessageFileSerializer(new FrontMatterSerializer);
});

it('writes id and posted_at into the frontmatter', function () {
    $output = $this->serializer->serialize('full', '2026-02-03 14:32:00', 'body');

    expect($output)
        ->toContain('id: full')
        ->toContain("posted_at: '2026-02-03 14:32:00'");
});

it('separates the frontmatter and body with a single blank line', function () {
    $output = $this->serializer->serialize('sep', '2026-01-01 00:00:00', 'Body content');

    expect($output)
        ->toStartWith("---\n")
        ->toContain("---\n\nBody content\n")
        ->toEndWith("Body content\n");
});

it('normalizes CRLF line endings in the body', function () {
    $output = $this->serializer->serialize('crlf', '2026-01-01 00:00:00', "\r\nMessage\r\nContent\r\n");

    expect($output)
        ->toContain("---\n\nMessage\nContent\n")
        ->not->toContain("\r");
});

it('trims surrounding blank lines from the body so repeated saves stay byte-stable', function () {
    $output = $this->serializer->serialize('trim', '2026-01-01 00:00:00', "\n\nMessage\nContent\n\n");

    expect($output)
        ->toContain("---\n\nMessage\nContent\n")
        ->toEndWith("Content\n")
        ->not->toContain("Content\n\n");
});
