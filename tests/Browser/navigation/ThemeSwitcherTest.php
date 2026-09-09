<?php

// Every scenario in theme_switcher.feature is decided entirely client-side
// (localStorage + inline script, per ADR 0005's precedent for the same
// component's older toggle) — the HTTP response is identical regardless of
// stored theme or menu state, so there is no HTTP-observable half of this
// feature. All scenarios are browser tests.

// theme_switcher.feature — Scenario Outline: The closed switcher always shows the auto icon, regardless of the active mode
it('always shows the auto icon on the closed switcher button, regardless of the active mode', function (string $mode) {
    $page = switchSiteThemeTo(visit('/'), $mode);

    $page->assertPresent('#theme-toggle '.themeModeIconSelector('auto'));
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

// theme_switcher.feature — Scenario: The closed switcher button has no visible border
it('shows no visible border on the closed switcher button', function () {
    $page = visit('/');

    $borderWidth = $page->script("parseFloat(getComputedStyle(document.querySelector('#theme-toggle')).borderTopWidth)");

    expect($borderWidth)->toBe(0);
});

// theme_switcher.feature — Scenario: Hovering the closed switcher button scales it up, the same way as the GitHub icon
it('scales up on hover like the GitHub icon, without changing its icon color', function () {
    $page = visit('/');

    $toggleColorBefore = $page->script("getComputedStyle(document.querySelector('#theme-toggle')).color");

    $page->hover('#theme-toggle');
    $toggleTransform = $page->script("getComputedStyle(document.querySelector('#theme-toggle')).transform");
    $toggleColorAfter = $page->script("getComputedStyle(document.querySelector('#theme-toggle')).color");

    $page->hover('a[aria-label="GitHub"]');
    $githubTransform = $page->script("getComputedStyle(document.querySelector('a[aria-label=\"GitHub\"]')).transform");

    expect($toggleTransform)->not->toBe('none');
    expect($toggleTransform)->toBe($githubTransform);
    expect($toggleColorAfter)->toBe($toggleColorBefore);
});

// theme_switcher.feature — Scenario: Opening the switcher lists all three modes in a fixed order, without icons
it('lists light, dark, then auto without icons when the switcher menu is opened', function () {
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

    $page->assertNotPresent('#theme-menu [role="option"] svg');
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

// theme_switcher.feature — Scenario Outline: Selecting a mode from the menu applies it and closes the menu, without changing the closed button's icon
it('applies the selected mode, closes the menu, and keeps showing the auto icon', function (string $from, string $to) {
    $page = switchSiteThemeTo(visit('/'), $from);
    $page->click('#theme-toggle');

    $page->click("[role=\"option\"][data-mode=\"{$to}\"]");

    $page->assertScript("localStorage.getItem('theme')", $to);
    $page->assertAttribute('#theme-toggle', 'aria-expanded', 'false');
    $page->assertPresent('#theme-toggle '.themeModeIconSelector('auto'));
})->with([
    'light to dark' => ['light', 'dark'],
    'dark to auto' => ['dark', 'auto'],
    'auto to light' => ['auto', 'light'],
]);

// theme_switcher.feature — Scenario: The chosen mode is remembered on the next visit, even though the button icon never reflects it
it('remembers the chosen mode as the persisted theme, while the button keeps showing the auto icon', function () {
    $page = switchSiteThemeTo(visit('/'), 'dark');

    $page->navigate('/');

    $page->assertScript("localStorage.getItem('theme')", 'dark');
    $page->assertPresent('#theme-toggle '.themeModeIconSelector('auto'));
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
