<?php

usesFakePostsRepository();

// homepage_about_section.feature — Scenario: The about-me section is shown above the post list
it('shows the about-me heading and bio above the post list', function () {
    configureAboutMe(heading: 'About me', bio: "Hi, I'm Cedriek.");
    writePostFile('welcome', 'Welcome', '2026-05-01');

    $body = $this->get('/')
        ->assertSuccessful()
        ->assertSee('About me')
        ->assertSee("Hi, I'm Cedriek.", escape: false)
        ->getContent();

    expect(strpos($body, 'About me'))->toBeLessThan(strpos($body, 'Welcome'));
});

// homepage_about_section.feature — Scenario: The bio is rendered from Markdown
it('renders the about-me bio from Markdown', function () {
    configureAboutMe(bio: 'I write about **software**.');

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('<strong>software</strong>', escape: false);
});

// homepage_about_section.feature — Scenario Outline: The section is shown whenever the heading or the bio has content
it('shows the about-me section when the heading or the bio has content', function (
    string $heading,
    string $bio,
    bool $visible,
) {
    configureAboutMe(heading: $heading, bio: $bio);

    $response = $this->get('/')->assertSuccessful();

    $visible
        ? $response->assertSee('data-section="about-me"', escape: false)
        : $response->assertDontSee('data-section="about-me"', escape: false);
})->with([
    'heading and bio' => ['About me', 'I build things.', true],
    'heading only' => ['About me', '', true],
    'bio only' => ['', 'I build things.', true],
    'both empty' => ['', '', false],
]);

// homepage_about_section.feature — Scenario: The section is shown even when no posts are published
it('shows the about-me section even when no posts are published', function () {
    configureAboutMe(heading: 'About me');

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('About me')
        ->assertSee('no posts yet', escape: false);
});
