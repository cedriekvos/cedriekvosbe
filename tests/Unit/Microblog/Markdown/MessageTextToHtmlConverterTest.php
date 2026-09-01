<?php

use App\Microblog\Markdown\MessageTextToHtmlConverter;

covers(MessageTextToHtmlConverter::class);

it('renders a message body as a paragraph with its URLs linked', function () {
    expect(messageTextToHtmlConverter()->convert('Zie https://php.net'))
        ->toContain('Zie <a')
        ->toContain('href="https://php.net"');
});

it('opens a link to another site in a new tab with noopener and noreferrer', function () {
    config(['app.url' => 'https://cedriekvos.test']);

    $html = messageTextToHtmlConverter()->convert('Zie https://php.net/manual');

    expect($html)->toContain('target="_blank"')
        ->toContain('noopener')
        ->toContain('noreferrer');
});

it('opens a link to this site itself in the same tab', function () {
    config(['app.url' => 'https://cedriekvos.test']);

    $html = messageTextToHtmlConverter()->convert('Zie https://cedriekvos.test/blog/welkom');

    expect($html)->toContain('href="https://cedriekvos.test/blog/welkom"')
        ->not->toContain('target=');
});

it('shows markdown syntax literally and escapes raw html', function () {
    expect(messageTextToHtmlConverter()->convert('**niet vet** <b>vet</b>'))
        ->toBe("<p>**niet vet** &lt;b&gt;vet&lt;/b&gt;</p>\n");
});

it('returns an empty string when the text cannot be converted', function () {
    // Invalid UTF-8 makes CommonMark throw, which MarkdownToHtmlConverter reports and swallows.
    expect(messageTextToHtmlConverter()->convert("\xC3\x28"))->toBe('');
});
