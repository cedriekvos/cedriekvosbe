<?php

use App\Blog\DraftSlug;
use App\Blog\Storage\PostFileStorage;
use Illuminate\Support\Facades\Storage;

covers(PostFileStorage::class);

beforeEach(function () {
    Storage::fake('posts');
    $this->storage = new PostFileStorage(new DraftSlug);
});

it('returns an empty array when there are no posts', function () {
    expect($this->storage->all())->toBe([]);
});

it('returns the slug of every markdown post', function () {
    Storage::disk('posts')->put('published.md', 'body');
    Storage::disk('posts')->put('draft-wip.md', 'body');

    expect(collect($this->storage->all())->sort()->values()->all())
        ->toBe(['draft-wip', 'published']);
});

it('ignores non-markdown files', function () {
    Storage::disk('posts')->put('notes.txt', 'not a post');
    Storage::disk('posts')->put('post.md', 'body');

    expect($this->storage->all())->toBe(['post']);
});

it('reads the contents of a post by slug', function () {
    Storage::disk('posts')->put('hello.md', 'the body');

    expect($this->storage->read('hello'))->toBe('the body');
});

it('returns an empty string when reading a slug that does not exist', function () {
    expect($this->storage->read('missing'))->toBe('');
});

it('reports existence by slug', function () {
    Storage::disk('posts')->put('here.md', 'body');

    expect($this->storage->exists('here'))->toBeTrue();
    expect($this->storage->exists('nope'))->toBeFalse();
});

it('writes a post by slug', function () {
    $this->storage->put('written', 'contents');

    expect(Storage::disk('posts')->get('written.md'))->toBe('contents');
});

it('deletes a post by slug', function () {
    Storage::disk('posts')->put('doomed.md', 'body');

    expect($this->storage->delete('doomed'))->toBeTrue();
    expect(Storage::disk('posts')->exists('doomed.md'))->toBeFalse();
});

it('returns false when deleting a post that does not exist', function () {
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
