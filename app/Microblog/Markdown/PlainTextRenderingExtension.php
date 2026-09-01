<?php

declare(strict_types=1);

namespace App\Microblog\Markdown;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Renderer\Block\DocumentRenderer;
use League\CommonMark\Renderer\Block\ParagraphRenderer;
use League\CommonMark\Renderer\Inline\TextRenderer;

/**
 * Renders a parsed document as plain-text paragraphs.
 *
 * This registers the renderers for the three nodes CommonMark produces on its
 * own — document, paragraph and text — and deliberately nothing else. Without
 * `CommonMarkCoreExtension` there is no parser for emphasis, headings, code,
 * lists or raw HTML, so every such character survives into a Text node and is
 * escaped on the way out: a message body reads back exactly as it was typed.
 * A soft line break survives in that same Text node, so it needs no renderer
 * of its own either.
 */
final readonly class PlainTextRenderingExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addRenderer(Document::class, new DocumentRenderer);
        $environment->addRenderer(Paragraph::class, new ParagraphRenderer);
        $environment->addRenderer(Text::class, new TextRenderer);
    }
}
