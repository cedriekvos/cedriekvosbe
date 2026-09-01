<?php

use App\Blog\Repositories\PostsRepository;
use Illuminate\Support\Facades\Storage;

covers(PostsRepository::class);

beforeEach(function () {
    Storage::fake('posts');
    $this->repository = app(PostsRepository::class);
});

it('writes a post and reads it back through the facade', function () {
    $this->repository->create(['title' => 'Hello', 'date' => '2026-05-13', 'excerpt' => 'ex'], '# Body', 'hello', isDraft: false);

    $post = $this->repository->find('hello');

    expect($post->slug)->toBe('hello')
        ->and($post->title)->toBe('Hello')
        ->and($post->body)->toContain('# Body');
});

it('does not introduce extra blank lines on repeated saves', function () {
    $attrs = ['title' => 'Example', 'date' => '2026-05-13', 'excerpt' => 'an excerpt'];

    $slug = $this->repository->create($attrs, "### Heading\nBody content", 'example', isDraft: false);
    $firstWrite = Storage::disk('posts')->get($slug.'.md');

    $this->repository->update($slug, $attrs, $this->repository->find($slug)->body, 'example', isDraft: false);
    $secondWrite = Storage::disk('posts')->get($slug.'.md');

    expect($secondWrite)->toBe($firstWrite);
    expect($firstWrite)->toContain("---\n\n### Heading");
});

it('returns published posts sorted by date descending and excludes drafts', function () {
    $this->repository->create(['title' => 'Older', 'date' => '2026-01-01'], 'older body', 'older', isDraft: false);
    $this->repository->create(['title' => 'Newest', 'date' => '2026-09-01'], 'newest body', 'newest', isDraft: false);
    $this->repository->create(['title' => 'Middle', 'date' => '2026-05-01'], 'middle body', 'middle', isDraft: false);
    $this->repository->create(['title' => 'Hidden', 'date' => '2026-12-01'], 'draft body', 'hidden', isDraft: true);

    $posts = $this->repository->allExcludeDrafts();

    expect($posts)->toHaveCount(3);
    expect(array_column($posts, 'slug'))->toBe(['newest', 'middle', 'older']);
});

it('lists drafts and published posts together with the is_draft flag set', function () {
    $this->repository->create(['title' => 'Published', 'date' => '2026-01-01'], 'pub', 'pub', isDraft: false);
    $this->repository->create(['title' => 'Drafty', 'date' => '2026-02-01'], 'd', 'drafty', isDraft: true);

    $posts = $this->repository->allIncludeDrafts();

    expect($posts)->toHaveCount(2);

    $bySlug = collect($posts)->keyBy('slug')->all();
    expect($bySlug['draft-drafty']->is_draft)->toBeTrue();
    expect($bySlug['pub']->is_draft)->toBeFalse();
});

it('reports existence of posts by slug', function () {
    $this->repository->create(['title' => 'Hi', 'date' => '2026-01-01'], 'body', 'hi', isDraft: false);

    expect($this->repository->exists('hi'))->toBeTrue();
    expect($this->repository->exists('nope'))->toBeFalse();
});

it('removes the underlying file when deleting an existing post', function () {
    $this->repository->create(['title' => 'Bye', 'date' => '2026-01-01'], 'body', 'bye', isDraft: false);

    expect($this->repository->delete('bye'))->toBeTrue();
    expect(Storage::disk('posts')->exists('bye.md'))->toBeFalse();
});

it('stores posts under storage/app/private/content/posts', function () {
    expect(config('filesystems.disks.posts.root'))->toBe(storage_path('app/private/content/posts'));
});
