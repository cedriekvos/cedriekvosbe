<?php

use App\Livewire\Admin\PageIndex;
use App\Pages\Repositories\PagesRepository;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

usesFakePagesRepository();
signsInAsEditor();

// page_list.feature — Scenario: Every page is listed, including drafts
it('lists published pages and drafts together, labelling drafts', function () {
    $repo = app(PagesRepository::class);
    $repo->create(['title' => 'Public page'], 'body', 'public-page', isDraft: false);
    $repo->create(['title' => 'Hidden draft'], 'body', 'hidden-draft', isDraft: true);

    Livewire::test(PageIndex::class)
        ->assertSee('Public page')
        ->assertSee('Hidden draft')
        ->assertSee('draft-hidden-draft')
        ->assertSee('draft');
});

// page_list.feature — Scenario: An empty list explains there are no pages yet
it('shows an empty state message when no pages exist', function () {
    Livewire::test(PageIndex::class)->assertSee('No pages yet.');
});

// page_list.feature — Scenario: Deleting a page
it('deletes a page and flashes a confirmation', function () {
    app(PagesRepository::class)->create(['title' => 'Goner'], 'body', 'goner', isDraft: false);

    Livewire::test(PageIndex::class)
        ->call('delete', 'goner')
        ->assertSee('Page [goner] deleted.');

    expect(Storage::disk('pages')->exists('goner.md'))->toBeFalse();
});

// page_list.feature — Scenario: Reaching the editor from the list
it('links to create a new page and to edit an existing one', function () {
    app(PagesRepository::class)->create(['title' => 'Welcome'], 'body', 'welcome', isDraft: false);

    Livewire::test(PageIndex::class)
        ->assertSee(route('admin.pages.create'))
        ->assertSee(route('admin.pages.edit', ['slug' => 'welcome']));
});

// page_list.feature — Scenario: The page list cannot be reached while signed out
it('requires authentication to view the index', function () {
    auth()->logout();

    $this->get(route('admin.pages.index'))->assertRedirect(route('login'));
});
