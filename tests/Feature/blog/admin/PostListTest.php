<?php

use App\Blog\Repositories\PostsRepository;
use App\Livewire\Admin\PostIndex;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

usesFakePostsRepository();
signsInAsEditor();

// post_list.feature — Scenario: Every post is listed, including drafts
it('lists published posts and drafts together, labelling drafts', function () {
    $repo = app(PostsRepository::class);
    $repo->create(['title' => 'Public', 'date' => '2026-01-01'], 'body', 'public', isDraft: false);
    $repo->create(['title' => 'Hidden', 'date' => '2026-02-01'], 'body', 'hidden', isDraft: true);

    Livewire::test(PostIndex::class)
        ->assertSee('Public')
        ->assertSee('Hidden')
        ->assertSee('draft-hidden')
        ->assertSee('draft');
});

// post_list.feature — Scenario: Each listed post shows its publication date as day/month/year
it('shows the publication date as day/month/year', function () {
    app(PostsRepository::class)->create(
        ['title' => 'Public update', 'date' => '2026-05-01'],
        'body',
        'public-update',
        isDraft: false,
    );

    Livewire::test(PostIndex::class)->assertSee('01/05/2026');
});

// post_list.feature — Scenario: An empty list explains there are no posts yet
it('shows an empty state message when no posts exist', function () {
    Livewire::test(PostIndex::class)->assertSee('No posts yet.');
});

// post_list.feature — Scenario: Deleting a post
it('deletes a post and flashes a confirmation', function () {
    app(PostsRepository::class)->create(
        ['title' => 'Goner', 'date' => '2026-01-01'],
        'body',
        'goner',
        isDraft: false,
    );

    Livewire::test(PostIndex::class)
        ->call('delete', 'goner')
        ->assertSee('Post [goner] deleted.');

    expect(Storage::disk('posts')->exists('goner.md'))->toBeFalse();
});

// post_list.feature — Scenario: Reaching the editor from the list
it('links to create a new post and to edit an existing one', function () {
    app(PostsRepository::class)->create(
        ['title' => 'Welcome', 'date' => '2026-01-01'],
        'body',
        'welcome',
        isDraft: false,
    );

    Livewire::test(PostIndex::class)
        ->assertSee(route('admin.posts.create'))
        ->assertSee(route('admin.posts.edit', ['slug' => 'welcome']));
});

// post_list.feature — Scenario: The post list cannot be reached while signed out
it('requires authentication to view the index', function () {
    auth()->logout();

    $this->get(route('admin.posts.index'))->assertRedirect(route('login'));
});
