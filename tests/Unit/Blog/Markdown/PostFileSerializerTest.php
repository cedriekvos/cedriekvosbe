<?php

use App\Blog\Markdown\PostFileSerializer;
use App\Markdown\FrontMatterSerializer;

covers(PostFileSerializer::class);

beforeEach(function () {
    $this->serializer = new PostFileSerializer(new FrontMatterSerializer);
});

it('writes slug, title, date and excerpt into the frontmatter when all are present', function () {
    $output = $this->serializer->serialize(
        'full',
        ['title' => 'Full', 'date' => '2026-02-03', 'excerpt' => 'Summary'],
        'body',
    );

    expect($output)
        ->toContain('slug: full')
        ->toContain('title: Full')
        ->toContain("date: '2026-02-03'")
        ->toContain('excerpt: Summary');
});

it('omits a null excerpt from the frontmatter', function () {
    $output = $this->serializer->serialize('no-excerpt', ['title' => 'NoExcerpt', 'date' => '2026-01-01', 'excerpt' => null], 'body');

    expect($output)->not->toContain('excerpt:');
});

it('omits an empty-string excerpt from the frontmatter', function () {
    $output = $this->serializer->serialize('empty-excerpt', ['title' => 'EmptyExcerpt', 'date' => '2026-01-01', 'excerpt' => ''], 'body');

    expect($output)->not->toContain('excerpt:');
});

it('writes featured into the frontmatter when true', function () {
    $output = $this->serializer->serialize(
        'featured',
        ['title' => 'Featured', 'date' => '2026-01-01', 'featured' => true],
        'body',
    );

    expect($output)->toContain('featured: true');
});

it('omits featured from the frontmatter when false', function () {
    $output = $this->serializer->serialize(
        'not-featured',
        ['title' => 'NotFeatured', 'date' => '2026-01-01', 'featured' => false],
        'body',
    );

    expect($output)->not->toContain('featured:');
});

it('omits featured from the frontmatter when missing', function () {
    $output = $this->serializer->serialize(
        'no-featured-key',
        ['title' => 'NoFeaturedKey', 'date' => '2026-01-01'],
        'body',
    );

    expect($output)->not->toContain('featured:');
});

it('separates the frontmatter and body with a single blank line', function () {
    $output = $this->serializer->serialize('sep', ['title' => 'Sep', 'date' => '2026-01-01'], 'Body content');

    expect($output)
        ->toStartWith("---\n")
        ->toContain("---\n\nBody content\n")
        ->toEndWith("Body content\n");
});

it('normalizes CRLF line endings in the body', function () {
    $output = $this->serializer->serialize('crlf', ['title' => 'CRLF', 'date' => '2026-01-01'], "\r\n# Body\r\nContent\r\n");

    expect($output)
        ->toContain("---\n\n# Body\nContent\n")
        ->not->toContain("\r");
});

it('trims surrounding blank lines from the body so repeated saves stay byte-stable', function () {
    $output = $this->serializer->serialize('trim', ['title' => 'Trim', 'date' => '2026-01-01'], "\n\n# Heading\nContent\n\n");

    expect($output)
        ->toContain("---\n\n# Heading\nContent\n")
        ->toEndWith("Content\n")
        ->not->toContain("Content\n\n");
});
