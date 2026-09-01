<?php

usesFakePostsRepository();

// post_detail.feature — Scenario: Viewing a published post
it('shows a published post at its slug, with Markdown-rendered content', function () {
    writePostFile('welcome', 'Welcome', '2026-05-01', 'A short introduction to begin', 'Content with **emphasis**.');

    $this->get('/blog/welcome')
        ->assertSuccessful()
        ->assertSee('Welcome')
        ->assertSee('01/05/2026')
        ->assertSee('<strong>emphasis</strong>', escape: false);
});

// post_detail.feature — Scenario: The page links back to the homepage
it('links back to the homepage', function () {
    writePostFile('welcome', 'Welcome', '2026-05-01');

    $this->get('/blog/welcome')
        ->assertSuccessful()
        ->assertSee('href="/"', escape: false);
});

// post_detail.feature — Scenario: Visiting an unknown post returns not found
it('returns a 404 for an unknown slug', function () {
    $this->get('/blog/does-not-exist')->assertNotFound();
});

// post_detail.feature — Scenario Outline: A slug that could address something other than a post returns not found
it('returns a 404 for a slug shaped like anything but a post slug', function (string $slug) {
    $this->get('/blog/'.$slug)->assertNotFound();
})->with([
    'parent directory' => '..',
    'encoded traversal' => '..%2F..%2F.env',
    'dotfile' => '.env',
    'uppercase' => 'Welcome',
    'underscore' => 'not_a_slug',
]);

// post_detail.feature — Scenario: Raw HTML in a post is escaped rather than rendered
it('escapes raw HTML in a post body instead of rendering it', function () {
    writePostFile('welcome', 'Welcome', '2026-05-01', null, '<script>alert(1)</script>');

    $body = $this->get('/blog/welcome')->assertSuccessful()->getContent();

    expect($body)->not->toContain('<script>alert(1)</script>')
        ->and($body)->toContain('&lt;script&gt;');
});

// post_detail.feature — Scenario: An unsafe link in a post is not rendered as a link
it('does not render a javascript link in a post body', function () {
    writePostFile('welcome', 'Welcome', '2026-05-01', null, '[click](javascript:alert(1))');

    expect($this->get('/blog/welcome')->assertSuccessful()->getContent())->not->toContain('javascript:');
});

// post_detail.feature — Scenario: The post's excerpt appears as a lead-in before the article content
it('shows the excerpt as a lead-in before the article content', function () {
    writePostFile('welcome', 'Welcome', '2026-05-01', 'A short introduction to begin', 'Content with **emphasis**.');

    $body = $this->get('/blog/welcome')->assertSuccessful()->getContent();

    expect($body)->toContain('A short introduction to begin')
        ->and(strpos($body, 'A short introduction to begin'))->toBeLessThan(strpos($body, '<strong>emphasis</strong>'));
});

// post_detail.feature — Scenario: A post without an excerpt shows no excerpt lead-in
it('shows no excerpt lead-in for a post without an excerpt', function () {
    writePostFile('welcome', 'Welcome', '2026-05-01', '');

    $body = $this->get('/blog/welcome')->assertSuccessful()->getContent();

    $headerEnd = strpos($body, '</header>');
    $articleStart = strpos($body, '<article');

    expect(substr($body, $headerEnd, $articleStart - $headerEnd))->not->toContain('<p');
});
