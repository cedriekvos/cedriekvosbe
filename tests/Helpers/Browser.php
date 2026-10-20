<?php

use Pest\Browser\Api\AwaitableWebpage;
use Pest\Browser\Api\PendingAwaitablePage;

/**
 * Convert a hex colour (e.g. "#8839ef") to the "rgb(r, g, b)" string a
 * browser reports for it via getComputedStyle().
 */
function hexToRgb(string $hex): string
{
    [$red, $green, $blue] = sscanf($hex, '#%02x%02x%02x');

    return "rgb({$red}, {$green}, {$blue})";
}

/**
 * JavaScript expression: how opaque the element matching $selector is drawn,
 * as its fill-opacity (always 1 outside SVG) multiplied by its own opacity and
 * that of every element above it. Playwright's visibility check, and so
 * assertVisible(), ignores opacity, so a logo faded out with `opacity: 0`
 * would otherwise still count as visible.
 */
function drawnOpacityScript(string $selector): string
{
    $selector = json_encode($selector);

    return "(() => { let el = document.querySelector({$selector}); let opacity = parseFloat(getComputedStyle(el).fillOpacity); for (; el; el = el.parentElement) { opacity *= parseFloat(getComputedStyle(el).opacity); } return opacity; })()";
}

/**
 * Set the site's stored theme preference directly via localStorage and reload
 * the page, so a browser scenario can start from a known mode without driving
 * the switcher UI to get there (the "Given I have switched the site theme to
 * X" precondition, shared with header_github_link.feature and
 * post_code_highlighting.feature, but here needing the real tri-state
 * light/dark/auto value rather than just prefers-color-scheme emulation).
 */
function switchSiteThemeTo(PendingAwaitablePage|AwaitableWebpage $page, string $mode): PendingAwaitablePage|AwaitableWebpage
{
    $page->script('localStorage.setItem('.json_encode('theme').', '.json_encode($mode).')');

    return $page->refresh();
}

/**
 * The bare label text a theme switcher menu option renders for a given mode
 * (documentation/leesmij/navigation/theme_switcher.md), used to assert what
 * the open menu's options display. Task 015 replaced each option's Unicode
 * glyph with an inline SVG icon, so the text content is the label alone —
 * the icon is asserted separately via themeModeIconSelector().
 */
function themeModeLabel(string $mode): string
{
    return match ($mode) {
        'light' => 'Light',
        'dark' => 'Dark',
        'auto' => 'Auto',
        default => throw new InvalidArgumentException("Unknown theme mode [{$mode}]."),
    };
}

/**
 * A CSS selector for an inline SVG icon carrying the given mode's
 * `data-mode` attribute (documentation/leesmij/navigation/theme_switcher.md).
 * Since task 016 only the closed button still renders an icon, and it always
 * renders the "auto" one regardless of the active mode; menu options no
 * longer carry icons at all.
 */
function themeModeIconSelector(string $mode): string
{
    return match ($mode) {
        'light', 'dark', 'auto' => "svg[data-mode=\"{$mode}\"]",
        default => throw new InvalidArgumentException("Unknown theme mode [{$mode}]."),
    };
}
