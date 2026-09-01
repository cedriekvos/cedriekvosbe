<?php

use App\Markdown\FrontMatterParser;

covers(FrontMatterParser::class);

beforeEach(function () {
    $this->parser = new FrontMatterParser;
});

it('splits fenced front matter from the body', function () {
    $document = $this->parser->parse("---\ntitle: Hello\ndate: '2026-01-01'\n---\n\n# Body\n");

    expect($document->frontMatter)->toBe(['title' => 'Hello', 'date' => '2026-01-01']);
    expect($document->body)->toBe("# Body\n");
});

it('keeps blank lines inside the body rather than trimming them away', function () {
    $document = $this->parser->parse("---\ntitle: Hello\n---\n\nFirst\n\nSecond\n");

    expect($document->body)->toBe("First\n\nSecond\n");
});

it('treats the whole input as body when there is no opening fence', function () {
    $document = $this->parser->parse("# Just a heading\n");

    expect($document->frontMatter)->toBe([]);
    expect($document->body)->toBe("# Just a heading\n");
});

it('treats an unterminated fence as plain body content', function () {
    $contents = "---\ntitle: Never closed\n\nStill just body.\n";

    $document = $this->parser->parse($contents);

    expect($document->frontMatter)->toBe([]);
    expect($document->body)->toBe($contents);
});

it('returns empty front matter when the fenced block holds no yaml', function () {
    $document = $this->parser->parse("---\n\n---\n\nBody only.\n");

    expect($document->frontMatter)->toBe([]);
    expect($document->body)->toBe("Body only.\n");
});

it('does not recognise a fence that starts later in the input', function () {
    $contents = "Intro\n---\ntitle: Hello\n---\n\nBody\n";

    $document = $this->parser->parse($contents);

    expect($document->frontMatter)->toBe([]);
    expect($document->body)->toBe($contents);
});

it('parses a closing fence that is the last line without a trailing newline', function () {
    $document = $this->parser->parse("---\ntitle: Hello\n---");

    expect($document->frontMatter)->toBe(['title' => 'Hello']);
    expect($document->body)->toBe('');
});

it('parses an empty front matter block whose fences are adjacent', function () {
    $document = $this->parser->parse("---\n---\n\nBody only.\n");

    expect($document->frontMatter)->toBe([]);
    expect($document->body)->toBe("Body only.\n");
});

it('ignores front matter that is a bare scalar rather than a map', function () {
    $document = $this->parser->parse("---\njust a string\n---\n\nBody\n");

    expect($document->frontMatter)->toBe([]);
    expect($document->body)->toBe("Body\n");
});

it('closes on the first fence line and leaves later ones in the body', function () {
    $document = $this->parser->parse("---\ntitle: Hello\n---\n\nBefore\n\n---\n\nAfter\n");

    expect($document->frontMatter)->toBe(['title' => 'Hello']);
    expect($document->body)->toBe("Before\n\n---\n\nAfter\n");
});

it('leaves crlf authored front matter unrecognised', function () {
    $contents = "---\r\ntitle: Hello\r\n---\r\n\r\n# Body\r\n";

    $document = $this->parser->parse($contents);

    expect($document->frontMatter)->toBe([]);
    expect($document->body)->toBe($contents);
});

it('starts the body on the line directly after the closing fence', function () {
    $document = $this->parser->parse("---\ntitle: Hello\n---\nBody starts right away.\n");

    expect($document->frontMatter)->toBe(['title' => 'Hello']);
    expect($document->body)->toBe("Body starts right away.\n");
});
