<?php

usesFakePostsRepository();

// post_code_highlighting.feature — Scenario Outline: A fenced code block with a recognized language is rendered with syntax highlighting
it('highlights a fenced code block written in a recognized language', function (string $language, string $code) {
    writePostFile('welcome', 'Welcome', '2026-05-01', 'Intro', "```{$language}\n{$code}\n```");

    $this->get('/blog/welcome')
        ->assertSuccessful()
        ->assertSee('<pre', escape: false)
        ->assertSee('<span class="hl-keyword">return</span>', escape: false);
})->with([
    'php' => ['php', 'return $value;'],
    'javascript' => ['javascript', 'return value;'],
    'bash' => ['bash', 'return 0'],
]);

// post_code_highlighting.feature — Scenario: A fenced code block without a language is rendered as plain code
it('renders a fenced code block without a language as plain, unhighlighted code', function () {
    writePostFile('welcome', 'Welcome', '2026-05-01', 'Intro', "```\nreturn value;\n```");

    $body = $this->get('/blog/welcome')->assertSuccessful()->getContent();

    expect($body)->toContain('<pre')
        ->and($body)->toContain('return value;')
        ->and($body)->not->toContain('class="hl-');
});

// post_code_highlighting.feature — Scenario: A fenced code block with an unrecognized language is rendered as plain code
it('renders a fenced code block with an unrecognized language as plain, unhighlighted code', function () {
    writePostFile('welcome', 'Welcome', '2026-05-01', 'Intro', "```cobol\nreturn value;\n```");

    $body = $this->get('/blog/welcome')->assertSuccessful()->getContent();

    expect($body)->toContain('<pre')
        ->and($body)->toContain('return value;')
        ->and($body)->not->toContain('class="hl-');
});

// post_code_highlighting.feature — Scenario: Inline code spans are never syntax highlighted
it('never highlights an inline code span, even one written with the highlighting opt-in prefix', function () {
    writePostFile('welcome', 'Welcome', '2026-05-01', 'Intro', 'Some text `{php}$variable` after.');

    $body = $this->get('/blog/welcome')->assertSuccessful()->getContent();

    expect($body)->toContain('{php}$variable')
        ->and($body)->not->toContain('class="hl-');
});

// post_code_highlighting.feature — Scenario: Code highlighting does not extend to the About bio
it('does not highlight a fenced code block in the About bio', function () {
    configureAboutMe(heading: 'About me', bio: "```php\nreturn \$value;\n```");

    $body = $this->get('/')->assertSuccessful()->getContent();

    expect($body)->not->toContain('class="hl-');
});
