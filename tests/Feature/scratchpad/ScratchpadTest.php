<?php

use App\Livewire\Admin\ScratchpadForm;
use Livewire\Livewire;

usesFakePostsRepository();
signsInAsEditor();

// scratchpad.feature — Scenario: The editor is pre-filled with the stored scratchpad content
it('pre-fills the editor with the stored content', function () {
    configureScratchpad('Remember to check backlinks.');

    Livewire::test(ScratchpadForm::class)
        ->assertSet('content', 'Remember to check backlinks.');
});

// scratchpad.feature — Scenario: Updating the scratchpad content
it('updates the scratchpad content and returns to the post list with a confirmation', function () {
    configureScratchpad('Old note.');

    Livewire::test(ScratchpadForm::class)
        ->set('content', 'New note.')
        ->call('save')
        ->assertRedirect(route('admin.posts.index'));

    expect(session('status'))->toBe('Scratchpad updated.');
    expect(storedScratchpad()->content)->toBe('New note.');
});

// scratchpad.feature — Scenario Outline: Any content can be saved, including empty
it('saves any content, including empty', function (string $content) {
    Livewire::test(ScratchpadForm::class)
        ->set('content', $content)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.posts.index'));

    expect(session('status'))->toBe('Scratchpad updated.');
    expect(storedScratchpad()->content)->toBe($content);
})->with([
    'some content' => ['Some notes here.'],
    'empty' => [''],
]);

// scratchpad.feature — Scenario: The content preserves Markdown exactly as written
it('preserves the content Markdown exactly as written', function () {
    $content = "Line one\n\n- point **a**\n- point b";

    Livewire::test(ScratchpadForm::class)
        ->set('content', $content)
        ->call('save');

    expect(storedScratchpad()->content)->toBe($content);
});

// scratchpad.feature — Scenario: The scratchpad is reachable from the admin header
it('links to the scratchpad from the admin post list page', function () {
    $this->get(route('admin.posts.index'))
        ->assertSuccessful()
        ->assertSee(route('admin.scratchpad.edit'));
});

// scratchpad.feature — Scenario: The scratchpad cannot be reached while signed out
it('requires authentication to reach the scratchpad', function () {
    auth()->logout();

    $this->get(route('admin.scratchpad.edit'))->assertRedirect(route('login'));
});
