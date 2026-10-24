<?php

use App\Livewire\Admin\MessageForm;
use App\Microblog\Repositories\MessagesRepository;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

covers(MessageForm::class);

beforeEach(function () {
    Storage::fake('microblog');
});

it('starts a new message with an empty body', function () {
    $component = Livewire::test(MessageForm::class);

    $component->assertSet('id', null)
        ->assertSet('body', '');
});

it('fills the form with the stored message', function () {
    writeMessageFile('existing', 'Hello world', '2026-01-01 09:00:00');

    $component = Livewire::test(MessageForm::class, ['id' => 'existing']);

    $component->assertSet('id', 'existing')
        ->assertSet('body', 'Hello world');
});

it('aborts with 404 when the message does not exist', function () {
    $component = Livewire::test(MessageForm::class, ['id' => 'missing']);

    $component->assertNotFound();
});

it('posts a new message, then navigates to the message list with a status', function () {
    $component = Livewire::test(MessageForm::class)
        ->set('body', 'Fresh thought')
        ->call('save');

    $component->assertRedirectToRoute('admin.messages.index');
    expect($component->effects)->toHaveKey('redirectUsingNavigate', true);
    expect(session('status'))->toBe('Message posted.');
    expect(array_column(app(MessagesRepository::class)->all(), 'body'))->toBe(['Fresh thought']);
});

it('updates the stored message, then navigates to the message list with a status', function () {
    writeMessageFile('existing', 'Origional typo', '2026-01-01 09:00:00');

    $component = Livewire::test(MessageForm::class, ['id' => 'existing'])
        ->set('body', 'Original, fixed')
        ->call('save');

    $component->assertRedirectToRoute('admin.messages.index');
    expect($component->effects)->toHaveKey('redirectUsingNavigate', true);
    expect(session('status'))->toBe('Message updated.');
    expect(app(MessagesRepository::class)->all())
        ->toHaveCount(1)
        ->{0}->id->toBe('existing')
        ->{0}->body->toBe('Original, fixed');
});

it('rejects a body that is empty or longer than 280 characters', function (string $body, string $rule) {
    $component = Livewire::test(MessageForm::class)
        ->set('body', $body)
        ->call('save');

    $component->assertHasErrors(['body' => $rule])
        ->assertNoRedirect();
    expect(app(MessagesRepository::class)->all())->toBe([]);
})->with([
    'empty' => ['', 'required'],
    '281 characters' => [str_repeat('a', 281), 'max'],
]);

it('accepts a body of exactly 280 characters', function () {
    $component = Livewire::test(MessageForm::class)
        ->set('body', str_repeat('a', 280))
        ->call('save');

    $component->assertHasNoErrors();
});
