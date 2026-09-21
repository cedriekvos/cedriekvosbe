<?php

use App\Pages\Repositories\PagesRepository;
use Illuminate\Support\Facades\Storage;

covers(PagesRepository::class);

beforeEach(function () {
    Storage::fake('pages');
    $this->repository = app(PagesRepository::class);
});

it('writes a page and reads it back through the facade', function () {
    $this->repository->create(['title' => 'Hello'], '# Body', 'hello', isDraft: false);

    $page = $this->repository->find('hello');

    expect($page->slug)->toBe('hello')
        ->and($page->title)->toBe('Hello')
        ->and($page->body)->toContain('# Body');
});

it('does not introduce extra blank lines on repeated saves', function () {
    $attrs = ['title' => 'Example'];

    $slug = $this->repository->create($attrs, "### Heading\nBody content", 'example', isDraft: false);
    $firstWrite = Storage::disk('pages')->get($slug.'.md');

    $this->repository->update($slug, $attrs, $this->repository->find($slug)->body, 'example', isDraft: false);
    $secondWrite = Storage::disk('pages')->get($slug.'.md');

    expect($secondWrite)->toBe($firstWrite);
    expect($firstWrite)->toContain("---\n\n### Heading");
});

it('returns published pages sorted by title and excludes drafts', function () {
    $this->repository->create(['title' => 'Zebra'], 'z body', 'zebra', isDraft: false);
    $this->repository->create(['title' => 'Apple'], 'a body', 'apple', isDraft: false);
    $this->repository->create(['title' => 'Mango'], 'm body', 'mango', isDraft: false);
    $this->repository->create(['title' => 'Hidden'], 'draft body', 'hidden', isDraft: true);

    $pages = $this->repository->allExcludeDrafts();

    expect($pages)->toHaveCount(3);
    expect(array_column($pages, 'slug'))->toBe(['apple', 'mango', 'zebra']);
});

it('lists drafts and published pages together with the is_draft flag set', function () {
    $this->repository->create(['title' => 'Published'], 'pub', 'pub', isDraft: false);
    $this->repository->create(['title' => 'Drafty'], 'd', 'drafty', isDraft: true);

    $pages = $this->repository->allIncludeDrafts();

    expect($pages)->toHaveCount(2);

    $bySlug = collect($pages)->keyBy('slug')->all();
    expect($bySlug['draft-drafty']->is_draft)->toBeTrue();
    expect($bySlug['pub']->is_draft)->toBeFalse();
});

it('reports existence of pages by slug', function () {
    $this->repository->create(['title' => 'Hi'], 'body', 'hi', isDraft: false);

    expect($this->repository->exists('hi'))->toBeTrue();
    expect($this->repository->exists('nope'))->toBeFalse();
});

it('removes the underlying file when deleting an existing page', function () {
    $this->repository->create(['title' => 'Bye'], 'body', 'bye', isDraft: false);

    expect($this->repository->delete('bye'))->toBeTrue();
    expect(Storage::disk('pages')->exists('bye.md'))->toBeFalse();
});

it('stores pages under storage/app/private/content/pages', function () {
    expect(config('filesystems.disks.pages.root'))->toBe(storage_path('app/private/content/pages'));
});
