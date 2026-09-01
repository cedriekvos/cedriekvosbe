<?php

use App\Livewire\Admin\MessageIndex;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

usesFakeMicroblogRepository();
usesFakePostsRepository();
signsInAsEditor();

// microblog_list.feature — Scenario: Every posted message is listed, newest first
it('lists every message newest first', function () {
    postMessagesInOrder(['First message', 'Second message', 'Third message']);

    Livewire::test(MessageIndex::class)
        ->assertSeeHtmlInOrder(['Third message', 'Second message', 'First message']);
});

// microblog_list.feature — Scenario: Each listed message shows when it was posted
it('shows the posted timestamp for each message', function () {
    postMessage('Hello world', '2026-05-01 14:32');

    Livewire::test(MessageIndex::class)->assertSee('01/05/2026 14:32');
});

// microblog_list.feature — Scenario: An empty list explains there are no messages yet
it('shows an empty state message when no messages exist', function () {
    Livewire::test(MessageIndex::class)->assertSee('Nog geen berichten.');
});

// microblog_list.feature — Scenario: Deleting a message
it('deletes a message, flashes a confirmation, and removes it from the homepage', function () {
    $id = postMessage('Goner');

    Livewire::test(MessageIndex::class)
        ->call('delete', $id)
        ->assertSee('Message deleted.');

    expect(Storage::disk('microblog')->exists($id.'.md'))->toBeFalse();

    $this->get('/')->assertSuccessful()->assertDontSee('Goner');
});

// microblog_list.feature — Scenario: Reaching the composer and editor from the list
it('links to post a new message and to edit an existing one', function () {
    $id = postMessage('Welcome');

    Livewire::test(MessageIndex::class)
        ->assertSee(route('admin.messages.create'))
        ->assertSee(route('admin.messages.edit', ['id' => $id]));
});

// microblog_list.feature — Scenario: The admin message list cannot be reached while signed out
it('requires authentication to view the message index', function () {
    auth()->logout();

    $this->get(route('admin.messages.index'))->assertRedirect(route('login'));
});
