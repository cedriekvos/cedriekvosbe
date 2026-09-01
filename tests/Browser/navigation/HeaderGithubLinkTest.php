<?php

// header_github_link.feature — Scenario Outline: The GitHub link is visible in both light and dark mode
it('renders the header GitHub icon in the current theme\'s foreground colour', function (bool $dark, string $expectedIconHex) {
    $page = $dark ? visit('/')->inDarkMode() : visit('/');

    $iconColor = $page->script("getComputedStyle(document.querySelector('a[aria-label=\"GitHub\"] svg')).fill");

    expect($iconColor)->toBe(hexToRgb($expectedIconHex));
})->with([
    'light' => [false, '#1f2328'],
    'dark' => [true, '#c9d1d9'],
]);

// header_github_link.feature — Scenario Outline: The GitHub link is visible in both light and dark mode
it('renders the header GitHub icon in visibly different colours between light and dark mode', function () {
    $lightColor = visit('/')->script("getComputedStyle(document.querySelector('a[aria-label=\"GitHub\"] svg')).fill");
    $darkColor = visit('/')->inDarkMode()->script("getComputedStyle(document.querySelector('a[aria-label=\"GitHub\"] svg')).fill");

    expect($lightColor)->not->toBe($darkColor);
});
