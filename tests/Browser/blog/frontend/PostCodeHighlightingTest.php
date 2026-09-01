<?php

usesFakePostsRepository();

// post_code_highlighting.feature — Scenario Outline: Highlighted code uses a distinct colour theme per site mode
it("highlights code using the site's designated colour theme for the current mode", function (bool $dark, string $expectedKeywordHex) {
    writePostFile('welcome', 'Welcome', '2026-05-01', 'Intro', "```php\nreturn \$value;\n```");

    $page = $dark
        ? visit('/blog/welcome')->inDarkMode()
        : visit('/blog/welcome');

    $page->assertScript(
        "getComputedStyle(document.querySelector('.hl-keyword')).color",
        hexToRgb($expectedKeywordHex),
    );
})->with([
    'light' => [false, '#8839ef'],
    'dark' => [true, '#cba6f7'],
]);

// post_code_highlighting.feature — Scenario: The light-mode and dark-mode syntax highlighting themes are visibly different
it('renders visibly different syntax highlighting colours in light and dark mode', function () {
    writePostFile('welcome', 'Welcome', '2026-05-01', 'Intro', "```php\nreturn \$value;\n```");

    $lightColor = visit('/blog/welcome')->script("getComputedStyle(document.querySelector('.hl-keyword')).color");
    $darkColor = visit('/blog/welcome')->inDarkMode()->script("getComputedStyle(document.querySelector('.hl-keyword')).color");

    expect($lightColor)->not->toBe($darkColor);
});
