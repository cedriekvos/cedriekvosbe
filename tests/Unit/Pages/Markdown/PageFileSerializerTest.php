<?php

use App\Markdown\FrontMatterSerializer;
use App\Pages\Markdown\PageFileSerializer;

covers(PageFileSerializer::class);

beforeEach(function () {
    $this->serializer = new PageFileSerializer(new FrontMatterSerializer);
});

it('writes slug and title into the frontmatter', function () {
    $output = $this->serializer->serialize('full', ['title' => 'Full'], 'body');

    expect($output)
        ->toContain('slug: full')
        ->toContain('title: Full');
});

it('separates the frontmatter and body with a single blank line', function () {
    $output = $this->serializer->serialize('sep', ['title' => 'Sep'], 'Body content');

    expect($output)
        ->toStartWith("---\n")
        ->toContain("---\n\nBody content\n")
        ->toEndWith("Body content\n");
});

it('normalizes CRLF line endings in the body', function () {
    $output = $this->serializer->serialize('crlf', ['title' => 'CRLF'], "\r\n# Body\r\nContent\r\n");

    expect($output)
        ->toContain("---\n\n# Body\nContent\n")
        ->not->toContain("\r");
});

it('trims surrounding blank lines from the body so repeated saves stay byte-stable', function () {
    $output = $this->serializer->serialize('trim', ['title' => 'Trim'], "\n\n# Heading\nContent\n\n");

    expect($output)
        ->toContain("---\n\n# Heading\nContent\n")
        ->toEndWith("Content\n")
        ->not->toContain("Content\n\n");
});
