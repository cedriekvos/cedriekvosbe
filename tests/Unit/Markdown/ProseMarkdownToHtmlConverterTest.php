<?php

use App\Markdown\ProseMarkdownToHtmlConverter;
use Illuminate\Support\Facades\Exceptions;
use League\CommonMark\Exception\UnexpectedEncodingException;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

covers(ProseMarkdownToHtmlConverter::class);

beforeEach(function () {
    $this->renderer = new ProseMarkdownToHtmlConverter(
        new CommonMarkCoreExtension,
        new ExternalLinkExtension,
    );
});

it('renders markdown to html', function () {
    expect($this->renderer->convert('**bold**'))->toContain('<strong>bold</strong>');
});

it('opens external links in a new window when an internal host is configured', function () {
    config(['app.url' => 'https://example.com']);
    $renderer = new ProseMarkdownToHtmlConverter(new CommonMarkCoreExtension, new ExternalLinkExtension);

    $html = $renderer->convert('[external](https://other.com)');

    expect($html)->toContain('target="_blank"');
});

it('does not open internal links in a new window', function () {
    config(['app.url' => 'https://example.com']);
    $renderer = new ProseMarkdownToHtmlConverter(new CommonMarkCoreExtension, new ExternalLinkExtension);

    $html = $renderer->convert('[internal](https://example.com/page)');

    expect($html)->not->toContain('target="_blank"');
});

it('returns an empty string when the markdown cannot be converted', function () {
    // Invalid UTF-8 makes CommonMark throw a CommonMarkException, which is caught.
    expect($this->renderer->convert("\xC3\x28"))->toBe('');
});

it('reports the exception when markdown conversion fails', function () {
    Exceptions::fake();

    expect($this->renderer->convert("\xC3\x28"))->toBe('');

    // Invalid UTF-8 surfaces as an UnexpectedEncodingException (a CommonMarkException).
    Exceptions::assertReported(UnexpectedEncodingException::class);
});
