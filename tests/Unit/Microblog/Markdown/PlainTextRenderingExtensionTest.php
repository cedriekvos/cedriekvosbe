<?php

use App\Microblog\Markdown\PlainTextRenderingExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\MarkdownConverter;

covers(PlainTextRenderingExtension::class);

function convertAsPlainText(string $text): string
{
    $environment = new Environment(['html_input' => 'escape']);
    $environment->addExtension(new PlainTextRenderingExtension);

    return trim((new MarkdownConverter($environment))->convert($text)->getContent());
}

it('renders text as a paragraph', function () {
    expect(convertAsPlainText('Just shipped a new feature!'))->toBe('<p>Just shipped a new feature!</p>');
});

it('leaves markdown syntax as literal text', function (string $text) {
    expect(convertAsPlainText($text))->toBe('<p>'.$text.'</p>');
})->with([
    'bold' => ['**niet vet**'],
    'italic' => ['_niet cursief_'],
    'heading' => ['# geen kop'],
    'inline code' => ['`geen code`'],
    'list item' => ['- geen lijst'],
    'link syntax' => ['[de docs](/docs)'],
]);

it('escapes raw html rather than rendering it', function () {
    expect(convertAsPlainText('<script>alert(1)</script>'))
        ->toBe('<p>&lt;script&gt;alert(1)&lt;/script&gt;</p>');
});

it('keeps a soft line break inside the same paragraph', function () {
    expect(convertAsPlainText("first\nsecond"))->toBe("<p>first\nsecond</p>");
});

it('renders each blank-line separated block as its own paragraph', function () {
    expect(convertAsPlainText("first\n\nsecond"))->toBe("<p>first</p>\n<p>second</p>");
});
