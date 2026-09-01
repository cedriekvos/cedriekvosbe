<?php

use App\Microblog\Markdown\PlainTextRenderingExtension;
use App\Microblog\Markdown\WebUrlAutolinkExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\MarkdownConverter;

covers(WebUrlAutolinkExtension::class);

/**
 * Convert text through an environment that autolinks web URLs. The plain-text
 * renderers come along because they are what turns the surrounding text into
 * output at all; only the linking behaviour is asserted on here.
 */
function convertWithAutolinks(string $text): string
{
    $environment = new Environment(['html_input' => 'escape', 'allow_unsafe_links' => false]);
    $environment->addExtension(new PlainTextRenderingExtension);
    $environment->addExtension(new WebUrlAutolinkExtension);

    return trim((new MarkdownConverter($environment))->convert($text)->getContent());
}

it('links a bare http or https URL to itself', function (string $url) {
    expect(convertWithAutolinks($url))->toBe('<p><a href="'.$url.'">'.$url.'</a></p>');
})->with([
    'https' => ['https://php.net/manual'],
    'http' => ['http://example.test/page'],
]);

it('links a scheme-less www URL to https while showing it as typed', function () {
    expect(convertWithAutolinks('www.php.net'))
        ->toBe('<p><a href="https://www.php.net">www.php.net</a></p>');
});

it('links every URL in the text and leaves the words around them alone', function () {
    expect(convertWithAutolinks('Zie https://php.net en https://laravel.com voor meer'))
        ->toBe('<p>Zie <a href="https://php.net">https://php.net</a> en <a href="https://laravel.com">https://laravel.com</a> voor meer</p>');
});

it('leaves trailing sentence punctuation outside the link', function (string $punctuation) {
    expect(convertWithAutolinks('Zie https://php.net'.$punctuation))
        ->toBe('<p>Zie <a href="https://php.net">https://php.net</a>'.$punctuation.'</p>');
})->with([
    'full stop' => ['.'],
    'comma' => [','],
    'exclamation mark' => ['!'],
    'question mark' => ['?'],
]);

it('never links anything that is not an http, https or www URL', function (string $text) {
    expect(convertWithAutolinks($text))->toBe('<p>'.$text.'</p>');
})->with([
    'javascript scheme' => ['javascript:alert(1)'],
    'ftp scheme' => ['ftp://files.example.test/x'],
    'mailto scheme' => ['mailto:cedriek@example.test'],
    'bare email address' => ['cedriek@example.test'],
    'host without a scheme or www' => ['example.test/pagina'],
]);
