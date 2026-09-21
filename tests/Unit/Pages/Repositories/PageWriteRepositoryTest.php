<?php

use App\Pages\Repositories\PageWriteRepository;
use Illuminate\Support\Facades\Storage;

covers(PageWriteRepository::class);

beforeEach(function () {
    Storage::fake('pages');
    $this->writer = app(PageWriteRepository::class);
});

it('creates a published page on disk and returns its slug', function () {
    $slug = $this->writer->create(['title' => 'Fresh'], 'body', 'fresh', isDraft: false);

    expect($slug)->toBe('fresh');
    expect(Storage::disk('pages')->exists('fresh.md'))->toBeTrue();
});

it('prefixes a draft page with draft- and returns the prefixed slug', function () {
    $slug = $this->writer->create(['title' => 'Draft'], 'body', 'wip', isDraft: true);

    expect($slug)->toBe('draft-wip');
    expect(Storage::disk('pages')->exists('draft-wip.md'))->toBeTrue();
});

it('throws when creating a page whose slug already exists', function () {
    $this->writer->create(['title' => 'First'], 'body', 'dup', isDraft: false);

    expect(fn () => $this->writer->create(['title' => 'Second'], 'body', 'dup', isDraft: false))
        ->toThrow(RuntimeException::class, 'already exists');
});

it('throws when updating a page that does not exist', function () {
    expect(fn () => $this->writer->update('ghost', ['title' => 't'], 'body', 'ghost', isDraft: false))
        ->toThrow(RuntimeException::class, 'does not exist');
});

it('throws when updating to a slug that collides with another page', function () {
    $this->writer->create(['title' => 'A'], 'a', 'a', isDraft: false);
    $this->writer->create(['title' => 'B'], 'b', 'b', isDraft: false);

    expect(fn () => $this->writer->update('a', ['title' => 'A'], 'a', 'b', isDraft: false))
        ->toThrow(RuntimeException::class, 'already exists');
});

it('removes the old file when an update renames the slug', function () {
    $this->writer->create(['title' => 'Old'], 'body', 'old-slug', isDraft: false);

    $slug = $this->writer->update('old-slug', ['title' => 'Old'], 'body', 'new-slug', isDraft: false);

    expect($slug)->toBe('new-slug');
    expect(Storage::disk('pages')->exists('old-slug.md'))->toBeFalse();
    expect(Storage::disk('pages')->exists('new-slug.md'))->toBeTrue();
});

it('keeps the file in place when an update does not change the slug', function () {
    $this->writer->create(['title' => 'Same'], 'original body', 'same', isDraft: false);

    $this->writer->update('same', ['title' => 'Same'], 'updated body', 'same', isDraft: false);

    expect(Storage::disk('pages')->files())->toBe(['same.md']);
    expect(Storage::disk('pages')->get('same.md'))->toContain('updated body');
});

it('flips the draft prefix when toggling is_draft on update', function () {
    $this->writer->create(['title' => 'Toggle'], 'body', 'toggle', isDraft: true);
    expect(Storage::disk('pages')->exists('draft-toggle.md'))->toBeTrue();

    $this->writer->update('draft-toggle', ['title' => 'Toggle'], 'body', 'toggle', isDraft: false);

    expect(Storage::disk('pages')->exists('draft-toggle.md'))->toBeFalse();
    expect(Storage::disk('pages')->exists('toggle.md'))->toBeTrue();
});

it('returns false when deleting a slug that does not exist', function () {
    expect($this->writer->delete('nothing'))->toBeFalse();
});

it('removes the underlying file when deleting an existing page', function () {
    $this->writer->create(['title' => 'Bye'], 'body', 'bye', isDraft: false);

    expect($this->writer->delete('bye'))->toBeTrue();
    expect(Storage::disk('pages')->exists('bye.md'))->toBeFalse();
});
