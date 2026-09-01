<?php

use App\Blog\Repositories\PostWriteRepository;
use Illuminate\Support\Facades\Storage;

covers(PostWriteRepository::class);

beforeEach(function () {
    Storage::fake('posts');
    $this->writer = app(PostWriteRepository::class);
});

it('creates a published post on disk and returns its slug', function () {
    $slug = $this->writer->create(['title' => 'Fresh', 'date' => '2026-01-01'], 'body', 'fresh', isDraft: false);

    expect($slug)->toBe('fresh');
    expect(Storage::disk('posts')->exists('fresh.md'))->toBeTrue();
});

it('prefixes a draft post with draft- and returns the prefixed slug', function () {
    $slug = $this->writer->create(['title' => 'Draft', 'date' => '2026-01-01'], 'body', 'wip', isDraft: true);

    expect($slug)->toBe('draft-wip');
    expect(Storage::disk('posts')->exists('draft-wip.md'))->toBeTrue();
});

it('throws when creating a post whose slug already exists', function () {
    $this->writer->create(['title' => 'First', 'date' => '2026-01-01'], 'body', 'dup', isDraft: false);

    expect(fn () => $this->writer->create(['title' => 'Second', 'date' => '2026-01-02'], 'body', 'dup', isDraft: false))
        ->toThrow(RuntimeException::class, 'already exists');
});

it('throws when updating a post that does not exist', function () {
    expect(fn () => $this->writer->update('ghost', ['title' => 't', 'date' => '2026-01-01'], 'body', 'ghost', isDraft: false))
        ->toThrow(RuntimeException::class, 'does not exist');
});

it('throws when updating to a slug that collides with another post', function () {
    $this->writer->create(['title' => 'A', 'date' => '2026-01-01'], 'a', 'a', isDraft: false);
    $this->writer->create(['title' => 'B', 'date' => '2026-01-02'], 'b', 'b', isDraft: false);

    expect(fn () => $this->writer->update('a', ['title' => 'A', 'date' => '2026-01-01'], 'a', 'b', isDraft: false))
        ->toThrow(RuntimeException::class, 'already exists');
});

it('removes the old file when an update renames the slug', function () {
    $this->writer->create(['title' => 'Old', 'date' => '2026-01-01'], 'body', 'old-slug', isDraft: false);

    $slug = $this->writer->update('old-slug', ['title' => 'Old', 'date' => '2026-01-01'], 'body', 'new-slug', isDraft: false);

    expect($slug)->toBe('new-slug');
    expect(Storage::disk('posts')->exists('old-slug.md'))->toBeFalse();
    expect(Storage::disk('posts')->exists('new-slug.md'))->toBeTrue();
});

it('keeps the file in place when an update does not change the slug', function () {
    $this->writer->create(['title' => 'Same', 'date' => '2026-01-01'], 'original body', 'same', isDraft: false);

    $this->writer->update('same', ['title' => 'Same', 'date' => '2026-01-01'], 'updated body', 'same', isDraft: false);

    expect(Storage::disk('posts')->files())->toBe(['same.md']);
    expect(Storage::disk('posts')->get('same.md'))->toContain('updated body');
});

it('flips the draft prefix when toggling is_draft on update', function () {
    $this->writer->create(['title' => 'Toggle', 'date' => '2026-01-01'], 'body', 'toggle', isDraft: true);
    expect(Storage::disk('posts')->exists('draft-toggle.md'))->toBeTrue();

    $this->writer->update('draft-toggle', ['title' => 'Toggle', 'date' => '2026-01-01'], 'body', 'toggle', isDraft: false);

    expect(Storage::disk('posts')->exists('draft-toggle.md'))->toBeFalse();
    expect(Storage::disk('posts')->exists('toggle.md'))->toBeTrue();
});

it('returns false when deleting a slug that does not exist', function () {
    expect($this->writer->delete('nothing'))->toBeFalse();
});

it('removes the underlying file when deleting an existing post', function () {
    $this->writer->create(['title' => 'Bye', 'date' => '2026-01-01'], 'body', 'bye', isDraft: false);

    expect($this->writer->delete('bye'))->toBeTrue();
    expect(Storage::disk('posts')->exists('bye.md'))->toBeFalse();
});
