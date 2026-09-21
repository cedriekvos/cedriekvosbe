<?php

use App\Markdown\FrontMatterParser;
use App\Pages\Markdown\PageFileParser;
use App\Pages\Markdown\PageMarkdownToHtmlConverter;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

covers(PageFileParser::class);

beforeEach(function () {
    $this->parser = new PageFileParser(
        new FrontMatterParser,
        new PageMarkdownToHtmlConverter(new CommonMarkCoreExtension, new ExternalLinkExtension),
    );
});

it('parses frontmatter and body from markdown contents', function () {
    $page = $this->parser->parse("---\ntitle: Hello\n---\n\n# Body\n", 'hello');

    expect($page['title'])->toBe('Hello');
    expect($page['body'])->toBe("# Body\n");
});

it('converts the body to html', function () {
    $page = $this->parser->parse("---\ntitle: T\n---\n\n**bold**\n", 'page');

    expect($page['content'])->toContain('<strong>bold</strong>');
});

it('falls back to the given slug when not set in frontmatter', function () {
    $page = $this->parser->parse("---\ntitle: T\n---\n\nbody\n", 'my-page');

    expect($page['slug'])->toBe('my-page');
});

it('uses the frontmatter slug over the fallback', function () {
    $page = $this->parser->parse("---\nslug: custom-slug\ntitle: T\n---\n\nbody\n", 'filename');

    expect($page['slug'])->toBe('custom-slug');
});

it('treats the whole input as body when there is no frontmatter', function () {
    $page = $this->parser->parse("# Just a heading\n", 'plain');

    expect($page['body'])->toBe("# Just a heading\n");
    expect($page['slug'])->toBe('plain');
});

it('treats an unterminated frontmatter fence as plain body content', function () {
    $contents = "---\ntitle: Never closed\n\nStill just body.\n";
    $page = $this->parser->parse($contents, 'unterminated');

    expect($page['body'])->toBe($contents);
    expect($page)->not->toHaveKey('title');
    expect($page['slug'])->toBe('unterminated');
});
