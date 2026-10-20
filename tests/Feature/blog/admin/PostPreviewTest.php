<?php

use App\Blog\Repositories\PostsRepository;
use App\Livewire\Admin\PostForm;
use App\Livewire\Admin\PostIndex;
use Livewire\Livewire;

usesFakePostsRepository();
signsInAsEditor();

// post_preview.feature — Scenario: The post list has no preview buttons
it('offers no preview button on the post list', function () {
    app(PostsRepository::class)->create(['title' => 'Welcome', 'date' => '2026-05-01'], 'body', 'welcome', isDraft: true);

    expect(htmlElements(Livewire::test(PostIndex::class)->html(), 'a[href$="/preview"]'))->toBeEmpty();
});

// post_preview.feature — Scenario Outline: A preview button is offered in the editor of a saved post
it('offers a new-tab preview button on the post editor', function (bool $isDraft) {
    app(PostsRepository::class)->create(['title' => 'Welcome', 'date' => '2026-05-01'], 'body', 'welcome', isDraft: $isDraft);

    $buttons = htmlElements(Livewire::test(PostForm::class, ['slug' => 'welcome'])->html(), 'a[href$="/preview"]');

    expect($buttons)->toHaveCount(1)
        ->and($buttons[0]->getAttribute('href'))->toBe(url('/admin/posts/welcome/preview'))
        ->and($buttons[0]->getAttribute('target'))->toBe('_blank');
})->with(['draft' => true, 'published' => false]);

// post_preview.feature — Scenario: The post creation form has no preview button
it('offers no preview button on the new post form', function () {
    expect(htmlElements(Livewire::test(PostForm::class)->html(), 'a[href$="/preview"]'))->toBeEmpty();
});

// post_preview.feature — Scenario Outline: Previewing a post shows it as visitors would see it
it('previews a post with title, date and Markdown-rendered content', function (bool $isDraft) {
    app(PostsRepository::class)->create(['title' => 'Welcome', 'date' => '2026-05-01'], 'Content with **emphasis**.', 'welcome', isDraft: $isDraft);

    $this->get('/admin/posts/welcome/preview')
        ->assertSuccessful()
        ->assertSee('Welcome')
        ->assertSee('01/05/2026')
        ->assertSee('<strong>emphasis</strong>', escape: false);
})->with(['draft' => true, 'published' => false]);

// post_preview.feature — Scenario: A preview is clearly marked as a preview
it('marks the post preview with a preview notice', function () {
    app(PostsRepository::class)->create(['title' => 'Welcome', 'date' => '2026-05-01'], 'body', 'welcome', isDraft: true);

    $this->get('/admin/posts/welcome/preview')
        ->assertSuccessful()
        ->assertSeeText('preview', escape: true);
});

// post_preview.feature — Scenario: A draft post stays hidden on its public URL
it('keeps a draft post hidden on its public URL even when signed in', function () {
    app(PostsRepository::class)->create(['title' => 'Welcome', 'date' => '2026-05-01'], 'body', 'welcome', isDraft: true);

    $this->get('/blog/welcome')->assertNotFound();
});

// post_preview.feature — Scenario: Previewing an unknown post returns not found
it('returns 404 when previewing an unknown post', function () {
    $this->get('/admin/posts/does-not-exist/preview')->assertNotFound();
});

// post_preview.feature — Scenario Outline: A slug that could address something other than a post returns not found
it('returns 404 for a preview slug shaped like anything but a post slug', function (string $slug) {
    $this->get('/admin/posts/'.$slug.'/preview')->assertNotFound();
})->with([
    'parent directory' => '..',
    'encoded traversal' => '..%2F..%2F.env',
    'dotfile' => '.env',
    'uppercase' => 'Welcome',
    'underscore' => 'not_a_slug',
]);

// post_preview.feature — Scenario: A preview cannot be opened while signed out
it('redirects a signed-out visitor to login without revealing the draft', function () {
    app(PostsRepository::class)->create(['title' => 'Secret draft title', 'date' => '2026-05-01'], 'body', 'welcome', isDraft: true);
    auth()->logout();

    $this->get('/admin/posts/welcome/preview')
        ->assertRedirect(route('login'))
        ->assertDontSee('Secret draft title');
});

// post_preview.feature — Scenario: Raw HTML in a previewed post is escaped rather than rendered
it('escapes raw HTML in a previewed post body', function () {
    app(PostsRepository::class)->create(['title' => 'Welcome', 'date' => '2026-05-01'], '<script>alert(1)</script>', 'welcome', isDraft: true);

    $body = $this->get('/admin/posts/welcome/preview')->assertSuccessful()->getContent();

    expect($body)->not->toContain('<script>alert(1)</script>')
        ->and($body)->toContain('&lt;script&gt;');
});
