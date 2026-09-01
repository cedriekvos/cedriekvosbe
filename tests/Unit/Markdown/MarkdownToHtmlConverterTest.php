<?php

use App\Markdown\MarkdownToHtmlConverter;
use Illuminate\Support\Facades\Exceptions;
use League\CommonMark\Exception\UnexpectedEncodingException;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

covers(MarkdownToHtmlConverter::class);

beforeEach(function () {
    $this->converter = fn (): MarkdownToHtmlConverter => new MarkdownToHtmlConverter([
        new CommonMarkCoreExtension,
        new ExternalLinkExtension,
    ]);
});

it('renders markdown to html', function () {
    expect(($this->converter)()->convert('**bold**'))->toContain('<strong>bold</strong>');
});

it('registers every extension it is given', function () {
    $html = (new MarkdownToHtmlConverter([new CommonMarkCoreExtension]))->convert('*em*');

    expect($html)->toContain('<em>em</em>');
});

it('escapes raw html in the source instead of trusting it', function () {
    $html = ($this->converter)()->convert('<script>alert(1)</script>');

    expect($html)->not->toContain('<script>')
        ->and($html)->toContain('&lt;script&gt;');
});

it('escapes inline html attributes that could carry an event handler', function () {
    $html = ($this->converter)()->convert('<img src=x onerror=alert(1)>');

    expect($html)->not->toContain('<img')
        ->and($html)->toContain('&lt;img');
});

it('drops javascript links rather than rendering them', function () {
    $html = ($this->converter)()->convert('[click](javascript:alert(1))');

    expect($html)->not->toContain('javascript:');
});

it('opens external links in a new window when an internal host is configured', function () {
    config(['app.url' => 'https://example.com']);

    expect(($this->converter)()->convert('[external](https://other.com)'))->toContain('target="_blank"');
});

it('does not open internal links in a new window', function () {
    config(['app.url' => 'https://example.com']);

    expect(($this->converter)()->convert('[internal](https://example.com/page)'))->not->toContain('target="_blank"');
});

it('treats a non-string app url as having no internal host', function () {
    config(['app.url' => null]);

    expect(($this->converter)()->convert('[external](https://other.com)'))->toContain('target="_blank"');
});

it('generates an excerpt from markdown', function () {
    expect(($this->converter)()->excerpt('**Hello** world'))->toBe('Hello world');
});

it('truncates excerpts that exceed the max length', function () {
    $excerpt = ($this->converter)()->excerpt(str_repeat('a', 200), 20);

    expect(mb_strlen($excerpt))->toBe(20)
        ->and($excerpt)->toEndWith('…');
});

it('truncates from the start of the text rather than dropping its first character', function () {
    expect(($this->converter)()->excerpt('abcdefghij', 5))->toBe('abcd…');
});

it('returns the full text when it is shorter than the max length', function () {
    expect(($this->converter)()->excerpt('Short text', 100))->toBe('Short text');
});

it('does not truncate text that is exactly the max length', function () {
    $exact = str_repeat('a', 20);

    expect(($this->converter)()->excerpt($exact, 20))->toBe($exact);
});

it('applies a default max length of 160 characters', function () {
    expect(($this->converter)()->excerpt(str_repeat('a', 160)))->toBe(str_repeat('a', 160))
        ->and(($this->converter)()->excerpt(str_repeat('a', 161)))->toBe(str_repeat('a', 159).'…');
});

it('collapses whitespace in excerpts', function () {
    expect(($this->converter)()->excerpt("line one\n\nline two"))->toBe('line one line two');
});

it('returns an empty string when the markdown cannot be converted', function () {
    // Invalid UTF-8 makes CommonMark throw a CommonMarkException, which is caught.
    expect(($this->converter)()->convert("\xC3\x28"))->toBe('');
});

it('reports the exception when markdown conversion fails', function () {
    Exceptions::fake();

    expect(($this->converter)()->convert("\xC3\x28"))->toBe('');

    Exceptions::assertReported(UnexpectedEncodingException::class);
});
