<?php

use App\Pages\DraftSlug;
use App\Pages\Storage\PageFileStorage;
use Illuminate\Support\Facades\Storage;

covers(PageFileStorage::class);

beforeEach(function () {
    Storage::fake('pages');
    $this->storage = new PageFileStorage(new DraftSlug);
});

it('returns an empty array when there are no pages', function () {
    expect($this->storage->all())->toBe([]);
});

it('returns the slug of every markdown page', function () {
    Storage::disk('pages')->put('published.md', 'body');
    Storage::disk('pages')->put('draft-wip.md', 'body');

    expect(collect($this->storage->all())->sort()->values()->all())
        ->toBe(['draft-wip', 'published']);
});

it('ignores non-markdown files', function () {
    Storage::disk('pages')->put('notes.txt', 'not a page');
    Storage::disk('pages')->put('page.md', 'body');

    expect($this->storage->all())->toBe(['page']);
});

it('reads the contents of a page by slug', function () {
    Storage::disk('pages')->put('hello.md', 'the body');

    expect($this->storage->read('hello'))->toBe('the body');
});

it('returns an empty string when reading a slug that does not exist', function () {
    expect($this->storage->read('missing'))->toBe('');
});

it('reports existence by slug', function () {
    Storage::disk('pages')->put('here.md', 'body');

    expect($this->storage->exists('here'))->toBeTrue();
    expect($this->storage->exists('nope'))->toBeFalse();
});

it('writes a page by slug', function () {
    $this->storage->put('written', 'contents');

    expect(Storage::disk('pages')->get('written.md'))->toBe('contents');
});

it('deletes a page by slug', function () {
    Storage::disk('pages')->put('doomed.md', 'body');

    expect($this->storage->delete('doomed'))->toBeTrue();
    expect(Storage::disk('pages')->exists('doomed.md'))->toBeFalse();
});

it('returns false when deleting a page that does not exist', function () {
    expect($this->storage->delete('ghost'))->toBeFalse();
});

it('recognises drafts by the slug prefix', function () {
    expect($this->storage->startsWithDraft('draft-wip'))->toBeTrue();
    expect($this->storage->startsWithDraft('published'))->toBeFalse();
});

it('builds the on-disk slug, prefixing drafts', function () {
    expect($this->storage->getSlugFor('hello', false))->toBe('hello');
    expect($this->storage->getSlugFor('hello', true))->toBe('draft-hello');
});
