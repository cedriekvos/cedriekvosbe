<?php

use App\Blog\Markdown\FencedCodeHighlightExtension;
use App\Blog\Markdown\HighlightedPostMarkdownToHtmlConverter;
use Illuminate\Support\Facades\Exceptions;
use League\CommonMark\Exception\UnexpectedEncodingException;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use Tempest\Highlight\Highlighter;

covers(HighlightedPostMarkdownToHtmlConverter::class);

function highlightedPostMarkdownToHtmlConverter(): HighlightedPostMarkdownToHtmlConverter
{
    return new HighlightedPostMarkdownToHtmlConverter(
        new CommonMarkCoreExtension,
        new ExternalLinkExtension,
        new FencedCodeHighlightExtension(new Highlighter),
    );
}

it('renders markdown to html', function () {
    expect(highlightedPostMarkdownToHtmlConverter()->convert('**bold**'))->toContain('<strong>bold</strong>');
});

it('opens external links in a new window when an internal host is configured', function () {
    config(['app.url' => 'https://example.com']);

    $html = highlightedPostMarkdownToHtmlConverter()->convert('[external](https://other.com)');

    expect($html)->toContain('target="_blank"');
});

it('does not open internal links in a new window', function () {
    config(['app.url' => 'https://example.com']);

    $html = highlightedPostMarkdownToHtmlConverter()->convert('[internal](https://example.com/page)');

    expect($html)->not->toContain('target="_blank"');
});

it('highlights a fenced code block written in a recognized language', function () {
    $html = highlightedPostMarkdownToHtmlConverter()->convert("```php\nreturn \$value;\n```");

    expect($html)->toContain('<span class="hl-keyword">return</span>');
});

it('renders a fenced code block without a recognized language as plain code', function () {
    $html = highlightedPostMarkdownToHtmlConverter()->convert("```cobol\nreturn value;\n```");

    expect($html)->toContain('<pre')
        ->and($html)->not->toContain('class="hl-');
});

it('never highlights an inline code span, even one written with the highlighting opt-in prefix', function () {
    $html = highlightedPostMarkdownToHtmlConverter()->convert('Some text `{php}$variable` after.');

    expect($html)->toContain('{php}$variable')
        ->and($html)->not->toContain('class="hl-');
});

it('generates an excerpt from markdown', function () {
    expect(highlightedPostMarkdownToHtmlConverter()->excerpt('**Hello** world'))->toBe('Hello world');
});

it('truncates excerpts that exceed the max length', function () {
    $long = str_repeat('a', 200);

    $excerpt = highlightedPostMarkdownToHtmlConverter()->excerpt($long, 20);

    expect(mb_strlen($excerpt))->toBe(20);
    expect($excerpt)->toEndWith('…');
});

it('returns the full text when it is shorter than the max length', function () {
    expect(highlightedPostMarkdownToHtmlConverter()->excerpt('Short text', 100))->toBe('Short text');
});

it('does not truncate text that is exactly the max length', function () {
    $exact = str_repeat('a', 20);

    $excerpt = highlightedPostMarkdownToHtmlConverter()->excerpt($exact, 20);

    expect($excerpt)->toBe($exact)
        ->and($excerpt)->not->toEndWith('…');
});

it('applies a default max length of 160 characters', function () {
    $converter = highlightedPostMarkdownToHtmlConverter();

    expect($converter->excerpt(str_repeat('a', 160)))->toBe(str_repeat('a', 160))
        ->and($converter->excerpt(str_repeat('a', 161)))->toBe(str_repeat('a', 159).'…');
});

it('truncates from the start of the text', function () {
    expect(highlightedPostMarkdownToHtmlConverter()->excerpt('abcdefghijklmnopqrstuvwxyz', 10))->toBe('abcdefghi…');
});

it('collapses whitespace in excerpts', function () {
    expect(highlightedPostMarkdownToHtmlConverter()->excerpt("line one\n\nline two"))->toBe('line one line two');
});

it('returns an empty string when the markdown cannot be converted', function () {
    // Invalid UTF-8 makes CommonMark throw a CommonMarkException, which is caught.
    expect(highlightedPostMarkdownToHtmlConverter()->convert("\xC3\x28"))->toBe('');
});

it('reports the exception when markdown conversion fails', function () {
    Exceptions::fake();

    expect(highlightedPostMarkdownToHtmlConverter()->convert("\xC3\x28"))->toBe('');

    // Invalid UTF-8 surfaces as an UnexpectedEncodingException (a CommonMarkException).
    Exceptions::assertReported(UnexpectedEncodingException::class);
});
