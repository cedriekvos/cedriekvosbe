<?php

use App\Blog\Markdown\PostMarkdownToHtmlConverter;
use Illuminate\Support\Facades\Exceptions;
use League\CommonMark\Exception\UnexpectedEncodingException;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

covers(PostMarkdownToHtmlConverter::class);

it('renders markdown to html', function () {
    $renderer = new PostMarkdownToHtmlConverter(
        new CommonMarkCoreExtension,
        new ExternalLinkExtension,
    );

    expect($renderer->convert('**bold**'))->toContain('<strong>bold</strong>');
});

it('opens external links in a new window when an internal host is configured', function () {
    config(['app.url' => 'https://example.com']);
    $renderer = new PostMarkdownToHtmlConverter(
        new CommonMarkCoreExtension,
        new ExternalLinkExtension,
    );

    $html = $renderer->convert('[external](https://other.com)');

    expect($html)->toContain('target="_blank"');
});

it('does not open internal links in a new window', function () {
    config(['app.url' => 'https://example.com']);
    $renderer = new PostMarkdownToHtmlConverter(
        new CommonMarkCoreExtension,
        new ExternalLinkExtension,
    );

    $html = $renderer->convert('[internal](https://example.com/page)');

    expect($html)->not->toContain('target="_blank"');
});

it('generates an excerpt from markdown', function () {
    $renderer = new PostMarkdownToHtmlConverter(
        new CommonMarkCoreExtension,
        new ExternalLinkExtension,
    );

    expect($renderer->excerpt('**Hello** world'))->toBe('Hello world');
});

it('truncates excerpts that exceed the max length', function () {
    $renderer = new PostMarkdownToHtmlConverter(
        new CommonMarkCoreExtension,
        new ExternalLinkExtension,
    );
    $long = str_repeat('a', 200);

    $excerpt = $renderer->excerpt($long, 20);

    expect(mb_strlen($excerpt))->toBe(20);
    expect($excerpt)->toEndWith('…');
});

it('returns the full text when it is shorter than the max length', function () {
    $renderer = new PostMarkdownToHtmlConverter(
        new CommonMarkCoreExtension,
        new ExternalLinkExtension,
    );

    expect($renderer->excerpt('Short text', 100))->toBe('Short text');
});

it('does not truncate text that is exactly the max length', function () {
    $renderer = new PostMarkdownToHtmlConverter(
        new CommonMarkCoreExtension,
        new ExternalLinkExtension,
    );
    $exact = str_repeat('a', 20);

    $excerpt = $renderer->excerpt($exact, 20);

    expect($excerpt)->toBe($exact)
        ->and($excerpt)->not->toEndWith('…');
});

it('applies a default max length of 160 characters', function () {
    $renderer = new PostMarkdownToHtmlConverter(
        new CommonMarkCoreExtension,
        new ExternalLinkExtension,
    );

    expect($renderer->excerpt(str_repeat('a', 160)))->toBe(str_repeat('a', 160))
        ->and($renderer->excerpt(str_repeat('a', 161)))->toBe(str_repeat('a', 159).'…');
});

it('truncates from the start of the text', function () {
    $renderer = new PostMarkdownToHtmlConverter(
        new CommonMarkCoreExtension,
        new ExternalLinkExtension,
    );

    expect($renderer->excerpt('abcdefghijklmnopqrstuvwxyz', 10))->toBe('abcdefghi…');
});

it('collapses whitespace in excerpts', function () {
    $renderer = new PostMarkdownToHtmlConverter(
        new CommonMarkCoreExtension,
        new ExternalLinkExtension,
    );

    expect($renderer->excerpt("line one\n\nline two"))->toBe('line one line two');
});

it('returns an empty string when the markdown cannot be converted', function () {
    $renderer = new PostMarkdownToHtmlConverter(
        new CommonMarkCoreExtension,
        new ExternalLinkExtension,
    );

    // Invalid UTF-8 makes CommonMark throw a CommonMarkException, which is caught.
    expect($renderer->convert("\xC3\x28"))->toBe('');
});

it('reports the exception when markdown conversion fails', function () {
    Exceptions::fake();
    $renderer = new PostMarkdownToHtmlConverter(
        new CommonMarkCoreExtension,
        new ExternalLinkExtension,
    );

    expect($renderer->convert("\xC3\x28"))->toBe('');

    // Invalid UTF-8 surfaces as an UnexpectedEncodingException (a CommonMarkException).
    Exceptions::assertReported(UnexpectedEncodingException::class);
});
