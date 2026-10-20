<?php

// header_linkedin_link.feature — Scenario Outline: The header shows a LinkedIn link on every public page
it('shows a header link to the LinkedIn profile on every public page', function (string $page) {
    if ($page === '/blog/welcome') {
        writePostFile('welcome', 'Welcome', '2026-05-01');
    }

    $html = (string) $this->get($page)->assertSuccessful()->getContent();

    expect(htmlElements($html, 'header a[href="https://www.linkedin.com/in/cedriekvos"]'))->not->toBeEmpty();
})->with([
    'homepage' => ['/'],
    'post detail page' => ['/blog/welcome'],
]);

// header_linkedin_link.feature — Scenario: The LinkedIn link is placed directly next to the GitHub link
it('places the header LinkedIn link directly after the GitHub link and keeps the GitHub target', function () {
    $html = (string) $this->get('/')->assertSuccessful()->getContent();

    expect(htmlElements($html, 'header a[href="https://github.com/cedriekvos"]'))->not->toBeEmpty();

    expect($html)->toMatch(
        '/<a\b[^>]*\bhref="https:\/\/github\.com\/cedriekvos"[^>]*>.*?<\/a>\s*<a\b[^>]*\bhref="https:\/\/www\.linkedin\.com\/in\/cedriekvos"/is'
    );
});

// header_linkedin_link.feature — Scenario: The LinkedIn link opens in a new tab without exposing the site to tab-nabbing
it("opens the header's LinkedIn link in a new tab carrying noopener and noreferrer", function () {
    $html = (string) $this->get('/')->assertSuccessful()->getContent();

    $link = htmlElement($html, 'header a[href="https://www.linkedin.com/in/cedriekvos"]');

    expect($link->getAttribute('target'))->toBe('_blank');

    $rel = explode(' ', (string) $link->getAttribute('rel'));

    foreach (['noopener', 'noreferrer'] as $expectedRel) {
        expect($rel)->toContain($expectedRel);
    }
});

// header_linkedin_link.feature — Scenario: The LinkedIn link has an accessible label for screen reader users
it("gives the header's LinkedIn link the accessible name \"LinkedIn\"", function () {
    $html = (string) $this->get('/')->assertSuccessful()->getContent();

    expect(htmlElement($html, 'header a[href="https://www.linkedin.com/in/cedriekvos"]')->getAttribute('aria-label'))->toBe('LinkedIn');
});

// header_linkedin_link.feature — Scenarios 05 and 06 (theme-dependent logo variant, logo images load)
// See tests/Browser/navigation/HeaderLinkedinLinkTest.php: these scenarios are
// client-render-dependent (computed visibility, image decoding) and are verified there instead.
