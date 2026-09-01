<?php

usesFakePostsRepository();
usesFakeMicroblogRepository();

// message_links.feature — Scenario Outline: A bare URL is rendered as a link showing the URL exactly as written
it('renders a bare URL as a link showing the URL exactly as written', function (
    string $body,
    string $linkText,
    string $href,
) {
    $links = htmlLinks(renderedMessageOnHomepage($body));

    expect($links)->toHaveCount(1);
    expect($links[0]['text'])->toBe($linkText);
    expect($links[0]['href'])->toBe($href);
})->with([
    'https URL' => ['https://php.net/manual', 'https://php.net/manual', 'https://php.net/manual'],
    'http URL' => ['http://example.test/page', 'http://example.test/page', 'http://example.test/page'],
    'schemeless www URL' => ['www.php.net', 'www.php.net', 'https://www.php.net'],
]);

// message_links.feature — Scenario: Only the URL becomes a link; the surrounding words stay plain text
it('links only the URL and leaves the surrounding words as plain text', function () {
    $html = renderedMessageOnHomepage('Bronnen: https://php.net/manual echt de moeite');

    $links = htmlLinks($html);

    expect($links)->toHaveCount(1);
    expect($links[0]['text'])->toBe('https://php.net/manual');
    expect(htmlTextOutsideLinks($html))
        ->toContain('Bronnen:')
        ->toContain('echt de moeite');
});

// message_links.feature — Scenario: Every URL in a message containing several URLs becomes its own link
it('turns every URL in a message into its own link', function () {
    $links = htmlLinks(renderedMessageOnHomepage('Zie https://php.net en https://laravel.com voor meer'));

    expect($links)->toHaveCount(2);
    expect(array_column($links, 'href'))->toBe(['https://php.net', 'https://laravel.com']);
});

// message_links.feature — Scenario Outline: Trailing punctuation after a URL is not part of the link
it('leaves trailing punctuation after a URL outside the link', function (string $punctuation) {
    $html = renderedMessageOnHomepage('Zie https://php.net/manual'.$punctuation);

    $links = htmlLinks($html);

    expect($links)->toHaveCount(1);
    expect($links[0]['text'])->toBe('https://php.net/manual');
    expect($links[0]['href'])->toBe('https://php.net/manual');
    expect(htmlText($html))->toContain('Zie https://php.net/manual'.$punctuation);
})->with([
    'full stop' => ['.'],
    'comma' => [','],
    'exclamation mark' => ['!'],
    'question mark' => ['?'],
]);

// message_links.feature — Scenario Outline: Markdown formatting in a message body is shown literally, never rendered
it('shows Markdown formatting in a message body literally instead of rendering it', function (string $body) {
    $html = renderedMessageOnHomepage($body);

    expect(htmlText($html))->toContain($body);
    expect($html)->not->toMatch('/<(strong|em|h1|h2|h3|code|pre|ul|ol|li|blockquote)\b/i');
})->with([
    'bold' => ['**niet vet**'],
    'italic' => ['_niet cursief_'],
    'heading' => ['# geen kop'],
    'inline code' => ['`geen code`'],
    'list item' => ['- geen lijst'],
]);

// message_links.feature — Scenario: Markdown link syntax is not honoured, but the URL inside it is still linked
it('ignores Markdown link syntax but still links the URL inside it', function () {
    $html = renderedMessageOnHomepage('Zie [de docs](https://php.net) voor meer');

    $links = htmlLinks($html);

    expect($links)->toHaveCount(1);
    expect($links[0]['text'])->toBe('https://php.net');
    expect($links[0]['href'])->toBe('https://php.net');
    expect(htmlTextOutsideLinks($html))->toContain('[de docs](');
});

// message_links.feature — Scenario Outline: Text that is not an http, https or www URL is never turned into a link
it('never turns text that is not an http, https or www URL into a link', function (string $body) {
    $html = renderedMessageOnHomepage($body);

    expect(htmlLinks($html))->toBeEmpty();
    expect(htmlText($html))->toContain($body);
})->with([
    'javascript scheme' => ['javascript:alert(1)'],
    'ftp scheme' => ['ftp://files.example.test/x'],
    'mailto scheme' => ['mailto:cedriek@example.test'],
    'bare email address' => ['cedriek@example.test'],
    'host without a scheme or www' => ['example.test/pagina'],
]);

// message_links.feature — Scenario: A link to an external site opens in a new tab
it('opens a link to an external site in a new tab carrying noopener and noreferrer', function () {
    config(['app.url' => 'https://cedriekvos.test']);

    $links = htmlLinks(renderedMessageOnHomepage('Zie https://php.net/manual'));

    expect($links)->toHaveCount(1);
    expect($links[0]['target'])->toBe('_blank');

    $rel = explode(' ', $links[0]['rel']);

    foreach (['noopener', 'noreferrer'] as $expectedRel) {
        expect($rel)->toContain($expectedRel);
    }
});

// message_links.feature — Scenario: A link to this site itself opens in the same tab
it('opens a link to this site itself in the same tab', function () {
    config(['app.url' => 'https://cedriekvos.test']);

    $url = 'https://cedriekvos.test/blog/welkom';

    $links = htmlLinks(renderedMessageOnHomepage('Zie '.$url));

    expect($links)->toHaveCount(1);
    expect($links[0]['href'])->toBe($url);
    expect($links[0]['target'])->toBe('');
});

// message_links.feature — Scenario: Raw HTML in a message body is shown as text, not rendered
it('escapes raw HTML in a message body instead of adding it to the page', function () {
    $body = '<script>alert(1)</script> en <b>vet</b>';

    $html = renderedMessageOnHomepage($body);

    expect(htmlText($html))->toContain($body);
    expect($html)->toContain('&lt;script&gt;');
    expect($html)->not->toContain('<script>alert(1)</script>');
    expect($html)->not->toContain('<b>vet</b>');
});

// message_links.feature — Scenario: A message without a URL is rendered exactly as before
it('renders a message without a URL exactly as written and without links', function () {
    $html = renderedMessageOnHomepage('Just shipped a new feature!');

    expect(htmlText($html))->toContain('Just shipped a new feature!');
    expect(htmlLinks($html))->toBeEmpty();
});
