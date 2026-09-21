<?php

use App\Livewire\Admin\PageForm;
use App\Pages\Repositories\PagesRepository;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

usesFakePagesRepository();
signsInAsEditor();

// page_form.feature — Scenario: A new page starts as a draft
it('starts a new page with an empty slug, marked as a draft', function () {
    Livewire::test(PageForm::class)
        ->assertSet('originalSlug', null)
        ->assertSet('slug', '')
        ->assertSet('title', '')
        ->assertSet('isDraft', true);
});

// page_form.feature — Scenario: Creating a published page
it('creates a new published page and flashes a status message', function () {
    Livewire::test(PageForm::class)
        ->set('title', 'About')
        ->set('slug', 'about')
        ->set('body', '# Hello')
        ->set('isDraft', false)
        ->call('save')
        ->assertRedirect(route('admin.pages.index'));

    expect(session('status'))->toBe('Page created.');
    expect(Storage::disk('pages')->exists('about.md'))->toBeTrue();
});

// page_form.feature — Scenario: Saving a draft prefixes the stored slug with "draft-"
it('prefixes the file with draft- when saving a new draft', function () {
    Livewire::test(PageForm::class)
        ->set('title', 'Draftie')
        ->set('slug', 'wip')
        ->set('body', 'b')
        ->set('isDraft', true)
        ->call('save');

    expect(Storage::disk('pages')->files())->toEqual(['draft-wip.md']);
});

// page_form.feature — Scenario: Editing an existing page
it('updates an existing page and flashes an update status', function () {
    app(PagesRepository::class)->create(['title' => 'Old'], 'old body', 'editable', isDraft: false);

    Livewire::test(PageForm::class, ['slug' => 'editable'])
        ->set('title', 'Updated title')
        ->call('save')
        ->assertRedirect(route('admin.pages.index'));

    expect(session('status'))->toBe('Page updated.');
    expect(Storage::disk('pages')->get('editable.md'))->toContain('Updated title');
});

// page_form.feature — Scenario: Required fields are validated
it('requires title, slug and body', function () {
    Livewire::test(PageForm::class)
        ->set('title', '')
        ->set('slug', '')
        ->set('body', '')
        ->call('save')
        ->assertHasErrors(['title', 'slug', 'body']);
});

// page_form.feature — Scenario Outline: The slug only accepts lowercase letters, numbers and hyphens
it('rejects slugs containing uppercase letters, spaces or unsupported characters', function (string $slug) {
    Livewire::test(PageForm::class)
        ->set('title', 'T')
        ->set('slug', $slug)
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

// page_form.feature — Scenario: A slug carrying the draft prefix is rejected
it('rejects a draft-prefixed slug so a published page cannot be filed as a draft', function () {
    Livewire::test(PageForm::class)
        ->set('title', 'Released')
        ->set('slug', 'draft-release-notes')
        ->set('body', 'b')
        ->set('isDraft', false)
        ->call('save')
        ->assertHasErrors(['slug']);

    expect(Storage::disk('pages')->files())->toBe([]);
});

// page_form.feature — Scenario: A slug that already exists is rejected
it('rejects creating a page whose slug already exists', function () {
    app(PagesRepository::class)->create(['title' => 'Taken'], 'body', 'taken', isDraft: false);

    Livewire::test(PageForm::class)
        ->set('title', 'Another')
        ->set('slug', 'taken')
        ->set('body', 'b')
        ->set('isDraft', false)
        ->call('save')
        ->assertHasErrors(['slug'])
        ->assertSee('A page with this slug already exists.');
});

// page_form.feature — Scenario: Opening the editor for a page that does not exist returns not found
it('aborts with 404 when mounting against a slug that does not exist', function () {
    Livewire::test(PageForm::class, ['slug' => 'ghost'])->assertStatus(404);
});

// page_form.feature — Scenario Outline: The page form cannot be reached while signed out
it('requires authentication to reach the form routes', function () {
    auth()->logout();

    $this->get(route('admin.pages.create'))->assertRedirect(route('login'));
    $this->get(route('admin.pages.edit', ['slug' => 'ghost']))->assertRedirect(route('login'));
});

// page_form.feature — Scenario: Leaving the title fills in an empty slug
it('fills in an empty slug from the title when the title field loses focus', function () {
    Livewire::test(PageForm::class)
        ->set('title', 'My Great Page')
        ->call('fillSlugFromTitle')
        ->assertSet('slug', 'my-great-page');
});

// page_form.feature — Scenario: Leaving the title does not overwrite an existing slug
it('does not overwrite an existing slug when the title field loses focus', function () {
    Livewire::test(PageForm::class)
        ->set('slug', 'custom-slug')
        ->set('title', 'My Great Page')
        ->call('fillSlugFromTitle')
        ->assertSet('slug', 'custom-slug');
});
