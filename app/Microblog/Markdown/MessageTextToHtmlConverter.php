<?php

declare(strict_types=1);

namespace App\Microblog\Markdown;

use App\Markdown\MarkdownToHtmlConverter;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

/**
 * Renders a message body: plain text with its bare web URLs turned into links,
 * and no Markdown at all. A message is a thought of at most 280 characters, not
 * a document, so every character other than a URL means what it says.
 *
 * The security posture and the external-link behaviour are the shared ones, so
 * a link in a message opens the same way as a link in a post or in the bio.
 */
final readonly class MessageTextToHtmlConverter
{
    private MarkdownToHtmlConverter $markdownToHtmlConverter;

    public function __construct(
        PlainTextRenderingExtension $plainTextRenderingExtension,
        WebUrlAutolinkExtension $webUrlAutolinkExtension,
        ExternalLinkExtension $externalLinkExtension,
    ) {
        $this->markdownToHtmlConverter = new MarkdownToHtmlConverter([
            $plainTextRenderingExtension,
            $webUrlAutolinkExtension,
            $externalLinkExtension,
        ]);
    }

    public function convert(string $text): string
    {
        return $this->markdownToHtmlConverter->convert($text);
    }
}
