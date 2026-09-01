<?php

use App\Livewire\Admin\AboutForm;
use Livewire\Livewire;

usesFakePostsRepository();
signsInAsEditor();

// about_form.feature — Scenario: The editor is pre-filled with the stored about content
it('pre-fills the editor with the stored heading and raw Markdown bio', function () {
    configureAboutMe(heading: 'About me', bio: 'I write about **Go**.');

    Livewire::test(AboutForm::class)
        ->assertSet('heading', 'About me')
        ->assertSet('bio', 'I write about **Go**.');
});

// about_form.feature — Scenario: Updating the about content
it('updates the about content and returns to the post list with a confirmation', function () {
    configureAboutMe(heading: 'About me', bio: 'Old bio.');

    Livewire::test(AboutForm::class)
        ->set('heading', 'Over mij')
        ->set('bio', 'Nieuwe bio.')
        ->call('save')
        ->assertRedirect(route('admin.posts.index'));

    expect(session('status'))->toBe('About updated.');

    $about = storedAbout();
    expect($about->heading)->toBe('Over mij');
    expect($about->bio_as_markdown)->toBe('Nieuwe bio.');
});

// about_form.feature — Scenario Outline: Any combination of heading and bio can be saved, including an empty about
it('saves any combination of heading and bio, including an empty about', function (string $heading, string $bio) {
    Livewire::test(AboutForm::class)
        ->set('heading', $heading)
        ->set('bio', $bio)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.posts.index'));

    expect(session('status'))->toBe('About updated.');

    $about = storedAbout();
    expect($about->heading)->toBe($heading)
        ->and($about->bio_as_markdown)->toBe($bio);
})->with([
    'heading and bio' => ['About me', 'I build things.'],
    'heading only' => ['About me', ''],
    'bio only' => ['', 'I build things.'],
    'both empty' => ['', ''],
]);

// about_form.feature — Scenario: The bio preserves Markdown exactly as written
it('preserves the bio Markdown exactly as written', function () {
    $bio = "Line one\n\n- point **a**\n- point b";

    Livewire::test(AboutForm::class)
        ->set('heading', 'About me')
        ->set('bio', $bio)
        ->call('save');

    expect(storedAbout()->bio_as_markdown)->toBe($bio);
});

// about_form.feature — Scenario: The about editor is reachable from the admin header
it('links to the about editor from the admin post list page', function () {
    $this->get(route('admin.posts.index'))
        ->assertSuccessful()
        ->assertSee(route('admin.about.edit'));
});

// about_form.feature — Scenario: The about editor cannot be reached while signed out
it('requires authentication to reach the about editor', function () {
    auth()->logout();

    $this->get(route('admin.about.edit'))->assertRedirect(route('login'));
});
