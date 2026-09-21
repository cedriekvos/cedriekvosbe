<?php

declare(strict_types=1);

namespace App\Pages\Markdown;

use App\Markdown\MarkdownToHtmlConverter;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

/**
 * Renders page Markdown without code highlighting — pages are static prose,
 * with no fenced code blocks to speak of. Mirrors App\Blog\Markdown\PostMarkdownToHtmlConverter.
 */
final readonly class PageMarkdownToHtmlConverter
{
    private MarkdownToHtmlConverter $markdownToHtmlConverter;

    public function __construct(
        CommonMarkCoreExtension $commonMarkCoreExtension,
        ExternalLinkExtension $externalLinkExtension,
    ) {
        $this->markdownToHtmlConverter = new MarkdownToHtmlConverter([
            $commonMarkCoreExtension,
            $externalLinkExtension,
        ]);
    }

    public function convert(string $markdown): string
    {
        return $this->markdownToHtmlConverter->convert($markdown);
    }
}
