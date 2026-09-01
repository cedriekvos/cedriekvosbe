<?php

use App\Blog\Markdown\FencedCodeHighlightExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Tempest\Highlight\Highlighter;

covers(FencedCodeHighlightExtension::class);

function convertWithFencedCodeHighlightExtension(string $markdown): string
{
    $environment = new Environment;
    $environment->addExtension(new CommonMarkCoreExtension);
    $environment->addExtension(new FencedCodeHighlightExtension(new Highlighter));

    return (new MarkdownConverter($environment))->convert($markdown)->getContent();
}

it('highlights a fenced code block written in a recognized language', function () {
    $html = convertWithFencedCodeHighlightExtension("```php\nreturn \$value;\n```");

    expect($html)->toContain('<span class="hl-keyword">return</span>');
});

it('does not highlight an inline code span, even one written with the highlighting opt-in prefix', function () {
    $html = convertWithFencedCodeHighlightExtension('Some text `{php}$variable` after.');

    expect($html)->not->toContain('class="hl-');
});
