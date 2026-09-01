<?php

use App\Blog\DraftSlug;
use App\Blog\Markdown\FencedCodeHighlightExtension;
use App\Blog\Markdown\HighlightedPostMarkdownToHtmlConverter;
use App\Blog\Markdown\PostFileParser;
use App\Blog\PostFactory;
use App\Blog\PostFilter;
use App\Blog\PostReadTimeCalculator;
use App\Blog\Repositories\PostSource;
use App\Blog\Storage\PostFileStorage;
use App\Markdown\FrontMatterParser;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use Tempest\Highlight\Highlighter;

covers(PostSource::class);

beforeEach(function () {
    Storage::fake('posts');
    $draftSlug = new DraftSlug;
    $this->source = new PostSource(
        new PostFileStorage($draftSlug),
        new PostFileParser(new FrontMatterParser, new HighlightedPostMarkdownToHtmlConverter(new CommonMarkCoreExtension, new ExternalLinkExtension, new FencedCodeHighlightExtension(new Highlighter)), new PostReadTimeCalculator),
        new PostFactory,
        new PostFilter($draftSlug),
    );
});

it('excludes drafts without reading their files', function () {
    writePostFile('published', 'Published', '2026-01-01');
    writePostFile('draft-wip', 'WIP', '2026-01-02');

    expect(array_column($this->source->allExcludingDrafts(), 'slug'))->toBe(['published']);
});

it('returns an empty array from allExcludingDrafts when every post is a draft', function () {
    writePostFile('draft-wip', 'WIP', '2026-01-02');

    expect($this->source->allExcludingDrafts())->toBe([]);
});

it('builds a post from each file and tags drafts by the slug prefix', function () {
    writePostFile('published', 'Published', '2026-01-01');
    writePostFile('draft-wip', 'WIP', '2026-01-02');

    $bySlug = collect($this->source->all())->keyBy('slug');

    expect($bySlug)->toHaveCount(2);
    expect($bySlug['published']->title)->toBe('Published');
    expect($bySlug['published']->is_draft)->toBeFalse();
    expect($bySlug['draft-wip']->is_draft)->toBeTrue();
});

it('returns an empty array when there are no posts', function () {
    expect($this->source->all())->toBe([]);
});

it('finds a single post by slug and tags its draft status', function () {
    writePostFile('draft-secret', 'Secret', '2026-01-01');

    $post = $this->source->find('draft-secret');

    expect($post->title)->toBe('Secret');
    expect($post->is_draft)->toBeTrue();
});

it('returns null from find when the post is absent', function () {
    expect($this->source->find('missing'))->toBeNull();
});

it('reports existence by slug', function () {
    writePostFile('hello', 'Hello', '2026-01-01');

    expect($this->source->exists('hello'))->toBeTrue();
    expect($this->source->exists('missing'))->toBeFalse();
});
