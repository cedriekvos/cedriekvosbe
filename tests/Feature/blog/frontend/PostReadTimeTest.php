<?php

usesFakePostsRepository();

// post_read_time.feature — Scenario: Read time appears next to the date on the homepage post list
it('shows the read time next to the post date on the homepage', function () {
    writePostFile('welcome', 'Welcome', '2026-01-01', null, wordsOfBody(50));

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('01/01/2026')
        ->assertSee('1 min read');
});

// post_read_time.feature — Scenario: Read time appears next to the date on the post detail page
it('shows the read time next to the date on the post detail page', function () {
    writePostFile('welcome', 'Welcome', '2026-01-01', null, wordsOfBody(400));

    $this->get('/blog/welcome')
        ->assertSuccessful()
        ->assertSee('01/01/2026')
        ->assertSee('2 min read');
});

// post_read_time.feature — Scenario Outline: Read time is the word count divided by 200 words per minute, rounded up
it('rounds the read time up to the nearest whole minute', function (int $wordCount, int $readTime) {
    writePostFile('welcome', 'Welcome', '2026-01-01', null, wordsOfBody($wordCount));

    $this->get('/')->assertSuccessful()->assertSee("{$readTime} min read");
})->with([
    '1 word' => [1, 1],
    '199 words' => [199, 1],
    '200 words' => [200, 1],
    '201 words' => [201, 2],
    '400 words' => [400, 2],
    '401 words' => [401, 3],
    '1000 words' => [1000, 5],
]);

// post_read_time.feature — Scenario: A post without any body text still shows a minimum read time
it('shows a minimum read time of 1 minute for a post with an empty body', function () {
    writePostFile('welcome', 'Welcome', '2026-01-01', null, '');

    $this->get('/')->assertSuccessful()->assertSee('1 min read');
});

// post_read_time.feature — Scenario: Read time is based on visible text, not markdown or HTML syntax
it('excludes markdown syntax and image alt text from the read time calculation', function () {
    $altText = wordsOfBody(250);
    writePostFile('welcome', 'Welcome', '2026-01-01', null, "![{$altText}](image.png)\n\nShort post");

    $this->get('/')->assertSuccessful()->assertSee('1 min read');
});
