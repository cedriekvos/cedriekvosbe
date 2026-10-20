<?php

declare(strict_types=1);

namespace App\Markdown;

use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

/**
 * Renders Markdown without code highlighting, for prose with no fenced code
 * blocks to speak of: the about-me bio and pages. Never give it the highlight
 * extension; posts highlight through their own converter (ADR 0009).
 */
final readonly class ProseMarkdownToHtmlConverter
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
