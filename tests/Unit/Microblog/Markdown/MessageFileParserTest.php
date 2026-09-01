<?php

use App\Markdown\FrontMatterParser;
use App\Microblog\Markdown\MessageFileParser;

covers(MessageFileParser::class);

beforeEach(function () {
    $this->parser = new MessageFileParser(new FrontMatterParser);
});

it('parses frontmatter and body from markdown contents', function () {
    $message = $this->parser->parse("---\nid: hello\nposted_at: '2026-01-01 00:00:00'\n---\n\nThe body\n", 'hello');

    expect($message['id'])->toBe('hello');
    expect($message['posted_at'])->toBe('2026-01-01 00:00:00');
    expect($message['body'])->toBe('The body');
});

it('drops the trailing newlines the file format adds around the body', function () {
    $message = $this->parser->parse("---\nid: hello\n---\n\nThe body\n\n\n", 'hello');

    expect($message['body'])->toBe('The body');
});

it('keeps newlines inside a multi-line body', function () {
    $message = $this->parser->parse("---\nid: hello\n---\n\nFirst line\nSecond line\n", 'hello');

    expect($message['body'])->toBe("First line\nSecond line");
});

it('keeps trailing spaces that are part of the body', function () {
    $message = $this->parser->parse("---\nid: hello\n---\n\nThe body  \n", 'hello');

    expect($message['body'])->toBe('The body  ');
});

it('falls back to the given id when not set in frontmatter', function () {
    $message = $this->parser->parse("---\nposted_at: '2026-01-01 00:00:00'\n---\n\nbody\n", 'my-id');

    expect($message['id'])->toBe('my-id');
});

it('uses the frontmatter id over the fallback', function () {
    $message = $this->parser->parse("---\nid: custom-id\nposted_at: '2026-01-01 00:00:00'\n---\n\nbody\n", 'filename');

    expect($message['id'])->toBe('custom-id');
});

it('treats the whole input as body when there is no frontmatter', function () {
    $message = $this->parser->parse("Just a message\n", 'plain');

    expect($message['body'])->toBe('Just a message');
    expect($message['id'])->toBe('plain');
});

it('treats an unterminated frontmatter fence as plain body content', function () {
    $contents = "---\nid: never-closed\n\nStill just body.\n";
    $message = $this->parser->parse($contents, 'unterminated');

    expect($message['body'])->toBe(rtrim($contents, "\n"));
    expect($message)->not->toHaveKey('posted_at');
    expect($message['id'])->toBe('unterminated');
});
