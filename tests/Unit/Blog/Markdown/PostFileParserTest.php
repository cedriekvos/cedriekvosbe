<?php

use App\Blog\Markdown\FencedCodeHighlightExtension;
use App\Blog\Markdown\HighlightedPostMarkdownToHtmlConverter;
use App\Blog\Markdown\PostFileParser;
use App\Blog\PostReadTimeCalculator;
use App\Markdown\FrontMatterParser;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use Tempest\Highlight\Highlighter;

covers(PostFileParser::class);

beforeEach(function () {
    $this->parser = new PostFileParser(
        new FrontMatterParser,
        new HighlightedPostMarkdownToHtmlConverter(new CommonMarkCoreExtension, new ExternalLinkExtension, new FencedCodeHighlightExtension(new Highlighter)),
        new PostReadTimeCalculator,
    );
});

it('parses frontmatter and body from markdown contents', function () {
    $post = $this->parser->parse("---\ntitle: Hello\ndate: '2026-01-01'\n---\n\n# Body\n", 'hello');

    expect($post['title'])->toBe('Hello');
    expect($post['date'])->toBe('2026-01-01');
    expect($post['body'])->toBe("# Body\n");
});

it('converts the body to html', function () {
    $post = $this->parser->parse("---\ntitle: T\ndate: '2026-01-01'\n---\n\n**bold**\n", 'post');

    expect($post['content'])->toContain('<strong>bold</strong>');
});

it('falls back to the given slug when not set in frontmatter', function () {
    $post = $this->parser->parse("---\ntitle: T\ndate: '2026-01-01'\n---\n\nbody\n", 'my-post');

    expect($post['slug'])->toBe('my-post');
});

it('uses the frontmatter slug over the fallback', function () {
    $post = $this->parser->parse("---\nslug: custom-slug\ntitle: T\ndate: '2026-01-01'\n---\n\nbody\n", 'filename');

    expect($post['slug'])->toBe('custom-slug');
});

it('generates an excerpt from the body when none is set in frontmatter', function () {
    $post = $this->parser->parse("---\ntitle: T\ndate: '2026-01-01'\n---\n\nSome body text.\n", 'post');

    expect($post['excerpt'])->toBe('Some body text.');
});

it('uses the frontmatter excerpt when present', function () {
    $post = $this->parser->parse("---\ntitle: T\ndate: '2026-01-01'\nexcerpt: Custom excerpt.\n---\n\nBody.\n", 'post');

    expect($post['excerpt'])->toBe('Custom excerpt.');
});

it('calculates the read time from the rendered content', function () {
    $body = trim(str_repeat('word ', 400))."\n";
    $post = $this->parser->parse("---\ntitle: T\ndate: '2026-01-01'\n---\n\n{$body}", 'post');

    expect($post['read_time_minutes'])->toBe(2);
});

it('treats the whole input as body when there is no frontmatter', function () {
    $post = $this->parser->parse("# Just a heading\n", 'plain');

    expect($post['body'])->toBe("# Just a heading\n");
    expect($post['slug'])->toBe('plain');
});

it('treats an unterminated frontmatter fence as plain body content', function () {
    $contents = "---\ntitle: Never closed\n\nStill just body.\n";
    $post = $this->parser->parse($contents, 'unterminated');

    expect($post['body'])->toBe($contents);
    expect($post)->not->toHaveKey('title');
    expect($post['slug'])->toBe('unterminated');
});
