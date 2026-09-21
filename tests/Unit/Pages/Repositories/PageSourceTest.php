<?php

use App\Markdown\FrontMatterParser;
use App\Pages\DraftSlug;
use App\Pages\Markdown\PageFileParser;
use App\Pages\Markdown\PageMarkdownToHtmlConverter;
use App\Pages\PageFactory;
use App\Pages\PageFilter;
use App\Pages\Repositories\PageSource;
use App\Pages\Storage\PageFileStorage;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

covers(PageSource::class);

beforeEach(function () {
    Storage::fake('pages');
    $draftSlug = new DraftSlug;
    $this->source = new PageSource(
        new PageFileStorage($draftSlug),
        new PageFileParser(new FrontMatterParser, new PageMarkdownToHtmlConverter(new CommonMarkCoreExtension, new ExternalLinkExtension)),
        new PageFactory,
        new PageFilter($draftSlug),
    );
});

it('excludes drafts without reading their files', function () {
    writePageFile('published', 'Published');
    writePageFile('draft-wip', 'WIP');

    expect(array_column($this->source->allExcludingDrafts(), 'slug'))->toBe(['published']);
});

it('returns an empty array from allExcludingDrafts when every page is a draft', function () {
    writePageFile('draft-wip', 'WIP');

    expect($this->source->allExcludingDrafts())->toBe([]);
});

it('builds a page from each file and tags drafts by the slug prefix', function () {
    writePageFile('published', 'Published');
    writePageFile('draft-wip', 'WIP');

    $bySlug = collect($this->source->all())->keyBy('slug');

    expect($bySlug)->toHaveCount(2);
    expect($bySlug['published']->title)->toBe('Published');
    expect($bySlug['published']->is_draft)->toBeFalse();
    expect($bySlug['draft-wip']->is_draft)->toBeTrue();
});

it('returns an empty array when there are no pages', function () {
    expect($this->source->all())->toBe([]);
});

it('finds a single page by slug and tags its draft status', function () {
    writePageFile('draft-secret', 'Secret');

    $page = $this->source->find('draft-secret');

    expect($page->title)->toBe('Secret');
    expect($page->is_draft)->toBeTrue();
});

it('returns null from find when the page is absent', function () {
    expect($this->source->find('missing'))->toBeNull();
});

it('reports existence by slug', function () {
    writePageFile('hello', 'Hello');

    expect($this->source->exists('hello'))->toBeTrue();
    expect($this->source->exists('missing'))->toBeFalse();
});
