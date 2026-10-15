<?php

// The header GitHub logo's colour is decided by the browser's CSS and the
// stored theme, so an HTTP response cannot show which colour it is drawn in or
// that it stays visible in each theme. These scenarios are asserted in a real
// browser.

// header_github_link.feature — Scenario Outline: The GitHub link is visible in both light and dark mode
it('renders the header GitHub icon in visibly different colours between light and dark mode', function () {
    $lightColor = visit('/')->script("getComputedStyle(document.querySelector('a[aria-label=\"GitHub\"] svg')).fill");
    $darkColor = visit('/')->inDarkMode()->script("getComputedStyle(document.querySelector('a[aria-label=\"GitHub\"] svg')).fill");

    expect($lightColor)->not->toBe($darkColor);
});

// header_github_link.feature — Scenario Outline: The GitHub logo is pure black in light mode and pure white in dark mode
it('draws the header GitHub logo in pure black or pure white to suit the active theme', function (string $mode, string $expectedLogoHex) {
    $page = switchSiteThemeTo(visit('/'), $mode);

    $logoColor = $page->script("getComputedStyle(document.querySelector('a[aria-label=\"GitHub\"] svg path')).fill");

    expect($logoColor)->toBe(hexToRgb($expectedLogoHex));
})->with([
    'light' => ['light', '#000000'],
    'dark' => ['dark', '#ffffff'],
]);
