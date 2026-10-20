<?php

use App\Livewire\Admin\PageForm;
use App\Livewire\Admin\PageIndex;
use App\Pages\Repositories\PagesRepository;
use Livewire\Livewire;

usesFakePagesRepository();
signsInAsEditor();

// page_preview.feature — Scenario: The page list has no preview buttons
it('offers no preview button on the page list', function () {
    app(PagesRepository::class)->create(['title' => 'About'], 'body', 'about', isDraft: true);

    expect(htmlElements(Livewire::test(PageIndex::class)->html(), 'a[href$="/preview"]'))->toBeEmpty();
});

// page_preview.feature — Scenario Outline: A preview button is offered in the editor of a saved page
it('offers a new-tab preview button on the page editor', function (bool $isDraft) {
    app(PagesRepository::class)->create(['title' => 'About'], 'body', 'about', isDraft: $isDraft);

    $buttons = htmlElements(Livewire::test(PageForm::class, ['slug' => 'about'])->html(), 'a[href$="/preview"]');

    expect($buttons)->toHaveCount(1)
        ->and($buttons[0]->getAttribute('href'))->toBe(url('/admin/pages/about/preview'))
        ->and($buttons[0]->getAttribute('target'))->toBe('_blank');
})->with(['draft' => true, 'published' => false]);

// page_preview.feature — Scenario: The page creation form has no preview button
it('offers no preview button on the new page form', function () {
    expect(htmlElements(Livewire::test(PageForm::class)->html(), 'a[href$="/preview"]'))->toBeEmpty();
});

// page_preview.feature — Scenario Outline: Previewing a page shows it as visitors would see it
it('previews a page with title and Markdown-rendered content', function (bool $isDraft) {
    app(PagesRepository::class)->create(['title' => 'About'], 'Content with **emphasis**.', 'about', isDraft: $isDraft);

    $this->get('/admin/pages/about/preview')
        ->assertSuccessful()
        ->assertSee('About')
        ->assertSee('<strong>emphasis</strong>', escape: false);
})->with(['draft' => true, 'published' => false]);

// page_preview.feature — Scenario: A preview is clearly marked as a preview
it('marks the page preview with a preview notice', function () {
    app(PagesRepository::class)->create(['title' => 'About'], 'body', 'about', isDraft: true);

    $this->get('/admin/pages/about/preview')
        ->assertSuccessful()
        ->assertSeeText('preview', escape: true);
});

// page_preview.feature — Scenario: A draft page stays hidden on its public URL
it('keeps a draft page hidden on its public URL even when signed in', function () {
    app(PagesRepository::class)->create(['title' => 'About'], 'body', 'about', isDraft: true);

    $this->get('/about')->assertNotFound();
});

// page_preview.feature — Scenario: Previewing an unknown page returns not found
it('returns 404 when previewing an unknown page', function () {
    $this->get('/admin/pages/does-not-exist/preview')->assertNotFound();
});

// page_preview.feature — Scenario Outline: A slug that could address something other than a page returns not found
it('returns 404 for a preview slug shaped like anything but a page slug', function (string $slug) {
    $this->get('/admin/pages/'.$slug.'/preview')->assertNotFound();
})->with([
    'parent directory' => '..',
    'encoded traversal' => '..%2F..%2F.env',
    'dotfile' => '.env',
    'uppercase' => 'About',
    'underscore' => 'not_a_slug',
]);

// page_preview.feature — Scenario: A preview cannot be opened while signed out
it('redirects a signed-out visitor to login without revealing the draft', function () {
    app(PagesRepository::class)->create(['title' => 'Secret draft title'], 'body', 'about', isDraft: true);
    auth()->logout();

    $this->get('/admin/pages/about/preview')
        ->assertRedirect(route('login'))
        ->assertDontSee('Secret draft title');
});

// page_preview.feature — Scenario: Raw HTML in a previewed page is escaped rather than rendered
it('escapes raw HTML in a previewed page body', function () {
    app(PagesRepository::class)->create(['title' => 'About'], '<script>alert(1)</script>', 'about', isDraft: true);

    $body = $this->get('/admin/pages/about/preview')->assertSuccessful()->getContent();

    expect($body)->not->toContain('<script>alert(1)</script>')
        ->and($body)->toContain('&lt;script&gt;');
});
