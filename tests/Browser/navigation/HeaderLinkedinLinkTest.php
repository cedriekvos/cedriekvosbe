<?php

// The header LinkedIn logo swaps between a black and a white image per theme,
// and each image must actually load: which variant is displayed is decided by
// the browser's CSS and the stored theme, and image decoding happens in the
// browser, so neither can be shown by an HTTP response. These scenarios are
// asserted in a real browser.

/**
 * JavaScript expression: whether the header LinkedIn link's logo variant
 * ("black" or "white") is rendered on screen (not display:none, not
 * visibility:hidden, non-zero size).
 */
function linkedinLogoDisplayedScript(string $variant): string
{
    $selector = json_encode('a[aria-label="LinkedIn"] img[src*="linkedin-'.$variant.'"]');

    return "(() => { const el = document.querySelector({$selector}); if (!el) { return false; } const style = getComputedStyle(el); return style.display !== 'none' && style.visibility !== 'hidden' && el.offsetWidth > 0 && el.offsetHeight > 0; })()";
}

// header_linkedin_link.feature — Scenario Outline: The LinkedIn link shows the official logo variant that suits the active theme
it('shows the logo variant that suits the active theme and hides the other', function (string $mode, string $shown, string $hidden) {
    $page = switchSiteThemeTo(visit('/'), $mode);

    $page->assertVisible('a[aria-label="LinkedIn"]');
    expect($page->script(linkedinLogoDisplayedScript($shown)))->toBeTrue();
    expect($page->script(linkedinLogoDisplayedScript($hidden)))->toBeFalse();
})->with([
    'light' => ['light', 'black', 'white'],
    'dark' => ['dark', 'white', 'black'],
]);

// header_linkedin_link.feature — Scenario Outline: Both official LinkedIn logo variants load successfully
it('loads each official LinkedIn logo variant without error', function (string $variant) {
    $page = visit('/');

    $selector = json_encode('a[aria-label="LinkedIn"] img[src*="linkedin-'.$variant.'"]');

    $page->assertPresent('a[aria-label="LinkedIn"] img[src*="linkedin-'.$variant.'"]');
    expect($page->script("(() => { const el = document.querySelector({$selector}); return el !== null && el.complete && el.naturalWidth > 0; })()"))->toBeTrue();
})->with([
    'black' => ['black'],
    'white' => ['white'],
]);
