<?php

usesFakePagesRepository();

// page_detail.feature — Scenario: Viewing a published page
it('shows a published page at its slug, with Markdown-rendered content', function () {
    writePageFile('about', 'About', 'Content with **emphasis**.');

    $this->get('/about')
        ->assertSuccessful()
        ->assertSee('About')
        ->assertSee('<strong>emphasis</strong>', escape: false);
});

// page_detail.feature — Scenario: The page links back to the homepage
it('links back to the homepage', function () {
    writePageFile('about', 'About');

    $this->get('/about')
        ->assertSuccessful()
        ->assertSee('href="/"', escape: false);
});

// page_detail.feature — Scenario: Visiting an unknown page returns not found
it('returns a 404 for an unknown slug', function () {
    $this->get('/does-not-exist')->assertNotFound();
});

// page_detail.feature — Scenario Outline: A slug that could address something other than a page returns not found
it('returns a 404 for a slug shaped like anything but a page slug', function (string $slug) {
    $this->get('/'.$slug)->assertNotFound();
})->with([
    'parent directory' => '..',
    'encoded traversal' => '..%2F..%2F.env',
    'dotfile' => '.env',
    'uppercase' => 'About',
    'underscore' => 'not_a_slug',
]);

// page_detail.feature — Scenario: Raw HTML in a page is escaped rather than rendered
it('escapes raw HTML in a page body instead of rendering it', function () {
    writePageFile('about', 'About', '<script>alert(1)</script>');

    $body = $this->get('/about')->assertSuccessful()->getContent();

    expect($body)->not->toContain('<script>alert(1)</script>')
        ->and($body)->toContain('&lt;script&gt;');
});

// page_detail.feature — Scenario: An unsafe link in a page is not rendered as a link
it('does not render a javascript link in a page body', function () {
    writePageFile('about', 'About', '[click](javascript:alert(1))');

    expect($this->get('/about')->assertSuccessful()->getContent())->not->toContain('javascript:');
});
