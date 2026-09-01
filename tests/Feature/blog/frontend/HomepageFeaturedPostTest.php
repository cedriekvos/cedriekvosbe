<?php

usesFakePostsRepository();

// homepage_featured_post.feature — Scenario: A featured post is marked with the Uitgelicht badge
it('marks a featured post with the Uitgelicht badge', function () {
    writePostFile('featured-post', 'Featured post', '2026-05-01', isFeatured: true);
    writePostFile('regular-post', 'Regular post', '2026-05-02');

    $body = $this->get('/')->assertSuccessful()->getContent();

    expect(postCardHtml($body, 'featured-post'))->toContain('Uitgelicht')
        ->and(postCardHtml($body, 'regular-post'))->not->toContain('Uitgelicht');
});

// homepage_featured_post.feature — Scenario: The badge follows the featured flag, not the post's position in the list
it('badges a featured post regardless of its position in the list', function () {
    writePostFile('newest-post', 'Newest post', '2026-06-01');
    writePostFile('older-featured', 'Older featured', '2026-05-01', isFeatured: true);

    $body = $this->get('/')->assertSuccessful()->getContent();

    expect(postCardHtml($body, 'newest-post'))->not->toContain('Uitgelicht')
        ->and(postCardHtml($body, 'older-featured'))->toContain('Uitgelicht');
});

// homepage_featured_post.feature — Scenario: More than one post can show the badge at the same time
it('badges every post that is marked as featured', function () {
    writePostFile('post-a', 'Post A', '2026-05-01', isFeatured: true);
    writePostFile('post-b', 'Post B', '2026-05-02', isFeatured: true);
    writePostFile('post-c', 'Post C', '2026-05-03');

    $body = $this->get('/')->assertSuccessful()->getContent();

    expect(postCardHtml($body, 'post-a'))->toContain('Uitgelicht')
        ->and(postCardHtml($body, 'post-b'))->toContain('Uitgelicht')
        ->and(postCardHtml($body, 'post-c'))->not->toContain('Uitgelicht');
});

// homepage_featured_post.feature — Scenario: No badge appears when no post is featured
it('shows no badge at all when no post is featured', function () {
    writePostFile('post-a', 'Post A', '2026-05-01');
    writePostFile('post-b', 'Post B', '2026-05-02');

    $this->get('/')
        ->assertSuccessful()
        ->assertDontSee('Uitgelicht');
});
