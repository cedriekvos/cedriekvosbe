<?php

use App\Livewire\Admin\AboutForm;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

covers(AboutForm::class);

beforeEach(function () {
    Storage::fake('meta');
});

it('fills the form with the stored heading and Markdown bio', function () {
    configureAboutMe(heading: 'About me', bio: 'I write about **Go**.');

    $component = Livewire::test(AboutForm::class);

    $component->assertSet('heading', 'About me')
        ->assertSet('bio', 'I write about **Go**.');
});

it('stores the heading and bio, then navigates back to the post list with a status', function () {
    configureAboutMe(heading: 'About me', bio: 'Old bio.');

    $component = Livewire::test(AboutForm::class)
        ->set('heading', 'Over mij')
        ->set('bio', 'Nieuwe bio.')
        ->call('save');

    $component->assertRedirectToRoute('admin.posts.index');
    expect($component->effects)->toHaveKey('redirectUsingNavigate', true);
    expect(session('status'))->toBe('About updated.');
    expect(storedAbout())
        ->heading->toBe('Over mij')
        ->bio_as_markdown->toBe('Nieuwe bio.');
});
