<?php

use App\Blog\Repositories\PostsRepository;
use App\Livewire\Admin\PostForm;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

usesFakePostsRepository();
signsInAsEditor();

// post_form.feature — Scenario: A new post starts as a draft dated today
it('defaults a new post to today and leaves the slug empty', function () {
    Livewire::test(PostForm::class)
        ->assertSet('originalSlug', null)
        ->assertSet('slug', '')
        ->assertSet('title', '')
        ->assertSet('isDraft', true)
        ->assertSet('date', now()->format('Y-m-d'));
});

// post_form.feature — Scenario: Creating a published post
it('creates a new published post and flashes a status message', function () {
    Livewire::test(PostForm::class)
        ->set('title', 'Fresh')
        ->set('slug', 'fresh')
        ->set('date', '2026-05-13')
        ->set('excerpt', 'short')
        ->set('body', '# Heading')
        ->set('isDraft', false)
        ->call('save')
        ->assertRedirect(route('admin.posts.index'));

    expect(session('status'))->toBe('Post created.');
    expect(Storage::disk('posts')->exists('fresh.md'))->toBeTrue();
});

// post_form.feature — Scenario: Saving a draft prefixes the stored slug with "draft-"
it('prefixes the file with draft- when saving a new draft', function () {
    Livewire::test(PostForm::class)
        ->set('title', 'Draftie')
        ->set('slug', 'draftie')
        ->set('date', '2026-05-13')
        ->set('body', 'b')
        ->set('isDraft', true)
        ->call('save');

    expect(Storage::disk('posts')->files())->toEqual(['draft-draftie.md']);
});

// post_form.feature — Scenario: Editing an existing post
it('updates an existing post and flashes an update status', function () {
    app(PostsRepository::class)->create(
        ['title' => 'Old', 'date' => '2026-01-01'],
        'old body',
        'updatable',
        isDraft: false,
    );

    Livewire::test(PostForm::class, ['slug' => 'updatable'])
        ->set('title', 'New title')
        ->call('save')
        ->assertRedirect(route('admin.posts.index'));

    expect(session('status'))->toBe('Post updated.');
    expect(Storage::disk('posts')->get('updatable.md'))->toContain('New title');
});

// post_form.feature — Scenario: Required fields are validated
it('requires title, slug, date and body', function () {
    Livewire::test(PostForm::class)
        ->set('title', '')
        ->set('slug', '')
        ->set('date', '')
        ->set('body', '')
        ->call('save')
        ->assertHasErrors(['title', 'slug', 'date', 'body']);
});

// post_form.feature — Scenario Outline: The slug only accepts lowercase letters, numbers and hyphens
it('rejects slugs containing uppercase letters, spaces or unsupported characters', function (string $slug) {
    Livewire::test(PostForm::class)
        ->set('title', 'T')
        ->set('slug', $slug)
        ->set('date', '2026-01-01')
        ->set('body', 'b')
        ->call('save')
        ->assertHasErrors(['slug']);
})->with([
    'uppercase' => 'Hello',
    'space' => 'hello world',
    'underscore' => 'hello_world',
    'literal-draft' => 'draft',
    'draft-prefixed' => 'draft-wip',
]);

// post_form.feature — Scenario: A slug carrying the draft prefix is rejected
it('rejects a draft-prefixed slug so a published post cannot be filed as a draft', function () {
    Livewire::test(PostForm::class)
        ->set('title', 'Released')
        ->set('slug', 'draft-release-notes')
        ->set('date', '2026-01-01')
        ->set('body', 'b')
        ->set('isDraft', false)
        ->call('save')
        ->assertHasErrors(['slug']);

    expect(Storage::disk('posts')->files())->toBe([]);
});

// post_form.feature — Scenario: A slug that already exists is rejected
it('rejects creating a post whose slug already exists', function () {
    app(PostsRepository::class)->create(
        ['title' => 'Taken', 'date' => '2026-01-01'],
        'body',
        'taken',
        isDraft: false,
    );

    Livewire::test(PostForm::class)
        ->set('title', 'Another')
        ->set('slug', 'taken')
        ->set('date', '2026-01-02')
        ->set('body', 'b')
        ->set('isDraft', false)
        ->call('save')
        ->assertHasErrors(['slug'])
        ->assertSee('A post with this slug already exists.');
});

// post_form.feature — Scenario: Opening the editor for a post that does not exist returns not found
it('aborts with 404 when mounting against a slug that does not exist', function () {
    Livewire::test(PostForm::class, ['slug' => 'missing'])->assertStatus(404);
});

// post_form.feature — Scenario: A new post starts as not featured by default
it('defaults a new post to not featured', function () {
    Livewire::test(PostForm::class)
        ->assertSet('isFeatured', false);
});

// post_form.feature — Scenario: Creating a post marked as featured
it('creates a post marked as featured', function () {
    Livewire::test(PostForm::class)
        ->set('title', 'My first post')
        ->set('slug', 'my-first-post')
        ->set('date', '2026-05-13')
        ->set('body', '# Hello')
        ->set('isDraft', false)
        ->set('isFeatured', true)
        ->call('save');

    expect(app(PostsRepository::class)->find('my-first-post')->is_featured)->toBeTrue();
});

// post_form.feature — Scenario: The featured toggle reflects the post's stored state when editing
it('reflects a featured post as featured when opening the editor', function () {
    app(PostsRepository::class)->create(
        ['title' => 'Editable', 'date' => '2026-01-01', 'featured' => true],
        'body',
        'editable',
        isDraft: false,
    );

    Livewire::test(PostForm::class, ['slug' => 'editable'])
        ->assertSet('isFeatured', true);
});

// post_form.feature — Scenario: Un-marking a featured post
it('unmarks a featured post as not featured', function () {
    app(PostsRepository::class)->create(
        ['title' => 'Editable', 'date' => '2026-01-01', 'featured' => true],
        'body',
        'editable',
        isDraft: false,
    );

    Livewire::test(PostForm::class, ['slug' => 'editable'])
        ->set('isFeatured', false)
        ->call('save');

    expect(app(PostsRepository::class)->find('editable')->is_featured)->toBeFalse();
});

// post_form.feature — Scenario: More than one post can be featured at the same time
it('keeps multiple posts featured independently', function () {
    app(PostsRepository::class)->create(
        ['title' => 'First featured', 'date' => '2026-01-01', 'featured' => true],
        'body',
        'first-featured',
        isDraft: false,
    );

    Livewire::test(PostForm::class)
        ->set('title', 'Second featured')
        ->set('slug', 'second-featured')
        ->set('date', '2026-05-14')
        ->set('body', '# Hello again')
        ->set('isDraft', false)
        ->set('isFeatured', true)
        ->call('save');

    $posts = app(PostsRepository::class);
    expect($posts->find('first-featured')->is_featured)->toBeTrue();
    expect($posts->find('second-featured')->is_featured)->toBeTrue();
});

// post_form.feature — Scenario: Leaving the title fills in an empty slug
it('fills in an empty slug from the title when the title field loses focus', function () {
    Livewire::test(PostForm::class)
        ->set('title', 'My Great Post')
        ->call('fillSlugFromTitle')
        ->assertSet('slug', 'my-great-post');
});

// post_form.feature — Scenario: Leaving the title does not overwrite an existing slug
it('does not overwrite an existing slug when the title field loses focus', function () {
    Livewire::test(PostForm::class)
        ->set('slug', 'custom-slug')
        ->set('title', 'My Great Post')
        ->call('fillSlugFromTitle')
        ->assertSet('slug', 'custom-slug');
});

// Supplementary coverage (no matching scenario) ----------------------------------

it('does not add blank lines when opening and saving without changes', function () {
    $repo = app(PostsRepository::class);
    $attrs = ['title' => 'Hello', 'date' => '2026-05-13', 'excerpt' => 'ex'];
    $repo->create($attrs, "# Body\nContent", 'hello', isDraft: false);

    $before = Storage::disk('posts')->get('hello.md');

    Livewire::test(PostForm::class, ['slug' => 'hello'])
        ->call('save');

    $after = Storage::disk('posts')->get('hello.md');

    expect($after)->toBe($before);
});

it('rejects an invalid date format', function () {
    Livewire::test(PostForm::class)
        ->set('title', 'T')
        ->set('slug', 'ok')
        ->set('date', '01-05-2026')
        ->set('body', 'b')
        ->call('save')
        ->assertHasErrors(['date']);
});

it('rejects an excerpt longer than 300 characters', function () {
    Livewire::test(PostForm::class)
        ->set('title', 'T')
        ->set('slug', 'ok')
        ->set('date', '2026-01-01')
        ->set('excerpt', str_repeat('a', 301))
        ->set('body', 'b')
        ->call('save')
        ->assertHasErrors(['excerpt']);
});

it('accepts an excerpt at exactly the 300-character limit', function () {
    Livewire::test(PostForm::class)
        ->set('title', 'T')
        ->set('slug', 'ok')
        ->set('date', '2026-01-01')
        ->set('excerpt', str_repeat('a', 300))
        ->set('body', 'b')
        ->call('save')
        ->assertHasNoErrors(['excerpt']);
});

it('rejects a title longer than 255 characters', function () {
    Livewire::test(PostForm::class)
        ->set('title', str_repeat('a', 256))
        ->set('slug', 'ok')
        ->set('date', '2026-01-01')
        ->set('body', 'b')
        ->call('save')
        ->assertHasErrors(['title']);
});

it('rejects a slug longer than 255 characters', function () {
    Livewire::test(PostForm::class)
        ->set('title', 'T')
        ->set('slug', str_repeat('a', 256))
        ->set('date', '2026-01-01')
        ->set('body', 'b')
        ->call('save')
        ->assertHasErrors(['slug']);
});

it('allows a draft with the same base slug as an existing published post', function () {
    $repo = app(PostsRepository::class);
    $repo->create(['title' => 'Public', 'date' => '2026-01-01'], 'public body', 'shared', isDraft: false);

    Livewire::test(PostForm::class)
        ->set('title', 'Draft version')
        ->set('slug', 'shared')
        ->set('date', '2026-01-02')
        ->set('body', 'd')
        ->set('isDraft', true)
        ->call('save')
        ->assertHasNoErrors();

    expect(Storage::disk('posts')->exists('draft-shared.md'))->toBeTrue();
    expect(Storage::disk('posts')->exists('shared.md'))->toBeTrue();
});

it('detects a collision when saving a draft whose draft- prefix already exists', function () {
    $repo = app(PostsRepository::class);
    $repo->create(['title' => 'Existing', 'date' => '2026-01-01'], 'body', 'taken', isDraft: true);

    Livewire::test(PostForm::class)
        ->set('title', 'Another draft')
        ->set('slug', 'taken')
        ->set('date', '2026-01-02')
        ->set('body', 'd')
        ->set('isDraft', true)
        ->call('save')
        ->assertHasErrors(['slug']);
});

it('rejects a save that would collide with an existing slug', function () {
    $repo = app(PostsRepository::class);
    $repo->create(['title' => 'First', 'date' => '2026-01-01'], 'a', 'first', isDraft: false);
    $repo->create(['title' => 'Second', 'date' => '2026-01-02'], 'b', 'second', isDraft: false);

    Livewire::test(PostForm::class, ['slug' => 'first'])
        ->set('slug', 'second')
        ->call('save')
        ->assertHasErrors(['slug']);

    expect(Storage::disk('posts')->exists('first.md'))->toBeTrue();
});

// post_form.feature — Scenario Outline: The post form cannot be reached while signed out
it('requires authentication to reach the form routes', function () {
    auth()->logout();

    $this->get(route('admin.posts.create'))->assertRedirect(route('login'));
    $this->get(route('admin.posts.edit', ['slug' => 'anything']))->assertRedirect(route('login'));
});
