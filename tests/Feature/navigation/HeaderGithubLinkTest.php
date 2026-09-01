<?php

// header_github_link.feature — Scenario Outline: The header shows a GitHub link on every public page
it('shows a header link to the GitHub profile on every public page', function (string $page) {
    if ($page === '/blog/welcome') {
        writePostFile('welcome', 'Welcome', '2026-05-01');
    }

    $html = (string) $this->get($page)->assertSuccessful()->getContent();

    expect(headerGithubLinkAttributes($html))->not->toBe('');
})->with([
    'homepage' => ['/'],
    'post detail page' => ['/blog/welcome'],
]);

// header_github_link.feature — Scenario: The GitHub link opens in a new tab without exposing the site to tab-nabbing
it("opens the header's GitHub link in a new tab carrying noopener and noreferrer", function () {
    $html = (string) $this->get('/')->assertSuccessful()->getContent();

    $attributes = headerGithubLinkAttributes($html);

    expect(htmlAttribute($attributes, 'target'))->toBe('_blank');

    $rel = explode(' ', htmlAttribute($attributes, 'rel'));

    foreach (['noopener', 'noreferrer'] as $expectedRel) {
        expect($rel)->toContain($expectedRel);
    }
});

// header_github_link.feature — Scenario: The GitHub link has an accessible label for screen reader users
it("gives the header's GitHub link the accessible name \"GitHub\"", function () {
    $html = (string) $this->get('/')->assertSuccessful()->getContent();

    expect(htmlAttribute(headerGithubLinkAttributes($html), 'aria-label'))->toBe('GitHub');
});

// header_github_link.feature — Scenario Outline: The GitHub link is visible in both light and dark mode
// See tests/Browser/navigation/HeaderGithubLinkTest.php: this scenario is
// genuinely client-render-dependent (ADR 0005) and is verified there instead.
