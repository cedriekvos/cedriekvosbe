<?php

use App\Livewire\Admin\ScratchpadForm;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

covers(ScratchpadForm::class);

beforeEach(function () {
    Storage::fake('meta');
});

it('fills the form with the stored scratchpad content', function () {
    configureScratchpad('Remember the milk.');

    $component = Livewire::test(ScratchpadForm::class);

    $component->assertSet('content', 'Remember the milk.');
});

it('stores the content, then navigates back to the post list with a status', function () {
    configureScratchpad('Old note.');

    $component = Livewire::test(ScratchpadForm::class)
        ->set('content', 'New note.')
        ->call('save');

    $component->assertRedirectToRoute('admin.posts.index');
    expect($component->effects)->toHaveKey('redirectUsingNavigate', true);
    expect(session('status'))->toBe('Scratchpad updated.');
    expect(storedScratchpad()->content)->toBe('New note.');
});
