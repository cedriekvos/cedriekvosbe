<?php

declare(strict_types=1);

namespace App\Blog\Markdown;

use App\Markdown\MarkdownToHtmlConverter;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

/**
 * Renders Markdown without code highlighting, for prose that has no fenced code
 * blocks to speak of (the about-me bio).
 */
final readonly class PostMarkdownToHtmlConverter
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

    public function excerpt(string $markdown, int $maxLength = 160): string
    {
        return $this->markdownToHtmlConverter->excerpt($markdown, $maxLength);
    }
}
