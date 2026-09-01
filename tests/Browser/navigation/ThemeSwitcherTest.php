<?php

// Every scenario in theme_switcher.feature is decided entirely client-side
// (localStorage + inline script, per ADR 0005's precedent for the same
// component's older toggle) — the HTTP response is identical regardless of
// stored theme or menu state, so there is no HTTP-observable half of this
// feature. All scenarios are browser tests.

// theme_switcher.feature — Scenario Outline: The closed switcher shows only the icon for the active mode
it('shows only the icon for the active mode on the closed switcher button', function (string $mode) {
    $page = switchSiteThemeTo(visit('/'), $mode);

    $page->assertPresent('#theme-toggle '.themeModeIconSelector($mode));
    expect(trim((string) $page->text('#theme-toggle')))->toBe('');
})->with([
    'light' => ['light'],
    'dark' => ['dark'],
    'auto' => ['auto'],
]);

// theme_switcher.feature — Scenario: The closed switcher icon is the same size as the GitHub icon
it("shows the closed switcher's icon at the same size as the header's GitHub icon", function () {
    $page = visit('/');

    $toggleIconWidth = $page->script("parseFloat(getComputedStyle(document.querySelector('#theme-toggle svg')).width)");
    $githubIconWidth = $page->script("parseFloat(getComputedStyle(document.querySelector('a[aria-label=\"GitHub\"] svg')).width)");

    expect($toggleIconWidth)->toBeGreaterThan(0);
    expect($toggleIconWidth)->toBe($githubIconWidth);
});

// theme_switcher.feature — Scenario: Opening the switcher lists all three modes in a fixed order
it('lists light, dark, then auto when the switcher menu is opened', function () {
    $page = visit('/')->click('#theme-toggle');

    $labels = $page->script(
        "Array.from(document.querySelectorAll('[role=\"option\"]')).map(el => el.textContent.trim())"
    );

    // Ignores a trailing checkmark on whichever option is active (scenario 02
    // is about order, not exact text — see theme_switcher.feature scenario 02).
    $labelsWithoutCheckmark = array_map(
        fn (string $label): string => trim(str_replace('✓', '', $label)),
        $labels,
    );

    expect($labelsWithoutCheckmark)->toBe([
        themeModeLabel('light'),
        themeModeLabel('dark'),
        themeModeLabel('auto'),
    ]);
});

// theme_switcher.feature — Scenario Outline: The active mode shows a visible checkmark inside the open menu
it('shows a checkmark and aria-checked only on the active mode inside the open menu', function (string $mode) {
    $page = switchSiteThemeTo(visit('/'), $mode);
    $page->click('#theme-toggle');

    foreach (['light', 'dark', 'auto'] as $candidate) {
        $isActive = $candidate === $mode;

        $page->assertAriaAttribute(
            "[role=\"option\"][data-mode=\"{$candidate}\"]",
            'checked',
            $isActive ? 'true' : 'false',
        );

        $optionText = (string) $page->text("[role=\"option\"][data-mode=\"{$candidate}\"]");
        expect(str_contains($optionText, '✓'))->toBe($isActive);
    }
})->with([
    'light' => ['light'],
    'dark' => ['dark'],
    'auto' => ['auto'],
]);

// theme_switcher.feature — Scenario Outline: Selecting a mode from the menu applies it and closes the menu
it('applies the selected mode, closes the menu, and shows only its icon', function (string $from, string $to) {
    $page = switchSiteThemeTo(visit('/'), $from);
    $page->click('#theme-toggle');

    $page->click("[role=\"option\"][data-mode=\"{$to}\"]");

    $page->assertScript("localStorage.getItem('theme')", $to);
    $page->assertAttribute('#theme-toggle', 'aria-expanded', 'false');
    $page->assertPresent('#theme-toggle '.themeModeIconSelector($to));
})->with([
    'light to dark' => ['light', 'dark'],
    'dark to auto' => ['dark', 'auto'],
    'auto to light' => ['auto', 'light'],
]);

// theme_switcher.feature — Scenario: The chosen mode is remembered on the next visit
it('remembers the chosen mode as its icon on the next visit', function () {
    $page = switchSiteThemeTo(visit('/'), 'dark');

    $page->navigate('/');

    $page->assertPresent('#theme-toggle '.themeModeIconSelector('dark'));
});

// theme_switcher.feature — Scenario: Clicking outside the open menu closes it without changing the theme
it('closes the menu without changing the theme when clicking outside it', function () {
    $page = switchSiteThemeTo(visit('/'), 'light');
    $page->click('#theme-toggle');

    $page->script("document.querySelector('main').click()");

    $page->assertAttribute('#theme-toggle', 'aria-expanded', 'false');
    $page->assertScript("localStorage.getItem('theme')", 'light');
});

// theme_switcher.feature — Scenario: Pressing Escape closes the menu and returns focus to the switcher button
it('closes the menu and returns focus to the button on Escape', function () {
    $page = visit('/');
    $page->click('#theme-toggle');

    $page->keys('[role="option"][data-mode="auto"]', 'Escape');

    $page->assertAttribute('#theme-toggle', 'aria-expanded', 'false');
    $page->assertScript("document.activeElement === document.querySelector('#theme-toggle')", true);
});

// theme_switcher.feature — Scenario: The switcher button opens the menu from the keyboard with focus on the active option
it('opens the menu from the keyboard with focus on the active option', function () {
    $page = switchSiteThemeTo(visit('/'), 'dark');

    $page->keys('#theme-toggle', 'Enter');

    $page->assertAttribute('#theme-toggle', 'aria-expanded', 'true');
    $page->assertScript(
        "document.activeElement === document.querySelector('[role=\"option\"][data-mode=\"dark\"]')",
        true,
    );
});

// theme_switcher.feature — Scenario: Arrow keys move focus between the menu options
it('moves focus between menu options with the arrow keys', function () {
    $page = switchSiteThemeTo(visit('/'), 'light');
    $page->click('#theme-toggle');

    $page->keys('[role="option"][data-mode="light"]', 'ArrowDown');
    $page->assertScript(
        "document.activeElement === document.querySelector('[role=\"option\"][data-mode=\"dark\"]')",
        true,
    );

    $page->keys('[role="option"][data-mode="dark"]', 'ArrowDown');
    $page->assertScript(
        "document.activeElement === document.querySelector('[role=\"option\"][data-mode=\"auto\"]')",
        true,
    );
});

// theme_switcher.feature — Scenario: Selecting a focused option from the keyboard applies it and closes the menu
it('applies the focused option and closes the menu on Enter', function () {
    $page = visit('/');
    $page->click('#theme-toggle');

    $page->keys('[role="option"][data-mode="auto"]', 'Enter');

    $page->assertScript("localStorage.getItem('theme')", 'auto');
    $page->assertAttribute('#theme-toggle', 'aria-expanded', 'false');
    $page->assertScript("document.activeElement === document.querySelector('#theme-toggle')", true);
});

// theme_switcher.feature — Scenario Outline: The switcher button has an accessible name and expanded state
it('reports its accessible name and expanded state', function (bool $open, string $expanded) {
    $page = visit('/');

    if ($open) {
        $page->click('#theme-toggle');
    }

    $page->assertAttribute('#theme-toggle', 'aria-label', 'Theme');
    $page->assertAttribute('#theme-toggle', 'aria-expanded', $expanded);
})->with([
    'closed' => [false, 'false'],
    'open' => [true, 'true'],
]);

// theme_switcher.feature — Scenario Outline: The switcher is visible and usable in both light and dark mode
it('is visible in both light and dark mode', function (bool $dark) {
    $page = $dark ? visit('/')->inDarkMode() : visit('/')->inLightMode();
    $page = switchSiteThemeTo($page, $dark ? 'dark' : 'light');

    $page->assertVisible('#theme-toggle');
})->with([
    'light' => [false],
    'dark' => [true],
]);
