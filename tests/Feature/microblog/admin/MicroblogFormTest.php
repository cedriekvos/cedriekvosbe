<?php

use App\Livewire\Admin\MessageForm;
use Livewire\Livewire;

usesFakeMicroblogRepository();
usesFakePostsRepository();
signsInAsEditor();

// microblog_form.feature — Scenario: Posting a message makes it immediately visible
it('posts a new message and flashes a confirmation', function () {
    Livewire::test(MessageForm::class)
        ->set('body', 'Just shipped a new feature!')
        ->call('save')
        ->assertRedirect(route('admin.messages.index'));

    expect(session('status'))->toBe('Message posted.');

    $this->get('/')->assertSuccessful()->assertSee('Just shipped a new feature!');
});

// microblog_form.feature — Scenario: A message body cannot be empty
it('rejects an empty message body', function () {
    Livewire::test(MessageForm::class)
        ->set('body', '')
        ->call('save')
        ->assertHasErrors(['body']);
});

// microblog_form.feature — Scenario Outline: The message body cannot exceed 280 characters (280 chars)
it('accepts a message body at exactly the 280 character limit', function () {
    Livewire::test(MessageForm::class)
        ->set('body', str_repeat('a', 280))
        ->call('save')
        ->assertHasNoErrors();

    expect(session('status'))->toBe('Message posted.');
});

// microblog_form.feature — Scenario Outline: The message body cannot exceed 280 characters (281 chars)
it('rejects a message body over the 280 character limit', function () {
    Livewire::test(MessageForm::class)
        ->set('body', str_repeat('a', 281))
        ->call('save')
        ->assertHasErrors(['body']);
});

// microblog_form.feature — Scenario: Editing an existing message updates its content
it('updates an existing message and flashes an update status', function () {
    $id = postMessage('Origional typo');

    Livewire::test(MessageForm::class, ['id' => $id])
        ->set('body', 'Original, fixed')
        ->call('save')
        ->assertRedirect(route('admin.messages.index'));

    expect(session('status'))->toBe('Message updated.');

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Original, fixed')
        ->assertDontSee('Origional typo');
});

// microblog_form.feature — Scenario: Editing a message does not change its position in the newest-first order
it('keeps a message in its original position when edited', function () {
    $ids = postMessagesInOrder(['First message', 'Second message']);

    Livewire::test(MessageForm::class, ['id' => $ids['First message']])
        ->set('body', 'First message, edited')
        ->call('save');

    $this->get('/')
        ->assertSuccessful()
        ->assertSeeInOrder(['Second message', 'First message, edited']);
});

// microblog_form.feature — Scenario: Opening the editor for a message that does not exist returns not found
it('aborts with 404 when mounting against a message id that does not exist', function () {
    Livewire::test(MessageForm::class, ['id' => 'does-not-exist'])->assertStatus(404);
});

// microblog_form.feature — Scenario Outline: The message composer cannot be reached while signed out
it('requires authentication to reach the composer routes', function () {
    auth()->logout();

    $this->get(route('admin.messages.create'))->assertRedirect(route('login'));
    $this->get(route('admin.messages.edit', ['id' => 'anything']))->assertRedirect(route('login'));
});
