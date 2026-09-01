<?php

declare(strict_types=1);

namespace App\Blog\Markdown;

use App\Markdown\MarkdownToHtmlConverter;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

/**
 * Renders post Markdown with syntax-highlighted fenced code blocks.
 */
final readonly class HighlightedPostMarkdownToHtmlConverter
{
    private MarkdownToHtmlConverter $markdownToHtmlConverter;

    public function __construct(
        CommonMarkCoreExtension $commonMarkCoreExtension,
        ExternalLinkExtension $externalLinkExtension,
        FencedCodeHighlightExtension $fencedCodeHighlightExtension,
    ) {
        $this->markdownToHtmlConverter = new MarkdownToHtmlConverter([
            $commonMarkCoreExtension,
            $externalLinkExtension,
            $fencedCodeHighlightExtension,
        ]);
    }

    public function convert(string $markdown): string
    {
        return $this->markdownToHtmlConverter->convert($markdown);
    }

    public function excerpt(string $markdown, int $maxLength = 160): string
    {
        return $this->markdownToHtmlConverter->excerpt($markdown, $maxLength);
    }
}
