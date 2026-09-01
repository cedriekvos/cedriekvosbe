<?php

usesFakePostsRepository();

// homepage_posts_list.feature — Scenario: Published posts are listed newest first
it('lists published posts newest first by date', function () {
    writePostFile('first-post', 'First post', '2026-01-15');
    writePostFile('second-post', 'Second post', '2026-03-02');
    writePostFile('third-post', 'Third post', '2026-05-20');

    $body = $this->get('/')->assertSuccessful()->getContent();

    $positions = [
        'Third post' => strpos($body, 'Third post'),
        'Second post' => strpos($body, 'Second post'),
        'First post' => strpos($body, 'First post'),
    ];

    expect($positions['Third post'])->toBeLessThan($positions['Second post'])
        ->and($positions['Second post'])->toBeLessThan($positions['First post']);
});

// homepage_posts_list.feature — Scenario: Posts published on the same date are ordered alphabetically by title
it('orders posts published on the same date alphabetically by title', function () {
    // Slugs are deliberately not in title order so the ordering is driven by the
    // title tie-break rather than the underlying file (slug) order.
    writePostFile('post-1', 'Cherry', '2026-05-10');
    writePostFile('post-2', 'Apple', '2026-05-10');
    writePostFile('post-3', 'Banana', '2026-05-10');

    $body = $this->get('/')->assertSuccessful()->getContent();

    $positions = [
        'Apple' => strpos($body, 'Apple'),
        'Banana' => strpos($body, 'Banana'),
        'Cherry' => strpos($body, 'Cherry'),
    ];

    expect($positions['Apple'])->toBeLessThan($positions['Banana'])
        ->and($positions['Banana'])->toBeLessThan($positions['Cherry']);
});

// homepage_posts_list.feature — Scenario: Each listed post shows its title, date, and excerpt
it('shows the title, publication date, and excerpt for each post, with a link to the post page', function () {
    writePostFile('welcome', 'Welcome', '2026-05-01', 'A short introduction to begin');

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Welcome')
        ->assertSee('01/05/2026')
        ->assertSee('A short introduction to begin')
        ->assertSee('href="/blog/welcome"', escape: false);
});

// homepage_posts_list.feature — Scenario Outline: The publication date is displayed as day/month/year
it('displays the publication date as day/month/year', function (string $storedDate, string $displayedDate) {
    writePostFile('example', 'Example', $storedDate);

    $this->get('/')->assertSuccessful()->assertSee($displayedDate);
})->with([
    '1 May 2026' => ['2026-05-01', '01/05/2026'],
    '3 November 2026' => ['2026-11-03', '03/11/2026'],
    '25 December 2026' => ['2026-12-25', '25/12/2026'],
]);

// homepage_posts_list.feature — Scenario: Draft posts are hidden from the homepage
it('hides draft posts from the homepage', function () {
    writePostFile('public-update', 'Public update', '2026-04-01');
    writePostFile('draft-work-in-progress', 'Work in progress', '2026-04-02');

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Public update')
        ->assertDontSee('Work in progress');
});
