<?php

declare(strict_types=1);

namespace App\Blog\Markdown;

use App\Blog\PostReadTimeCalculator;
use App\Markdown\FrontMatterParser;

final readonly class PostFileParser
{
    public function __construct(
        private FrontMatterParser $frontMatterParser,
        private HighlightedPostMarkdownToHtmlConverter $highlightedPostMarkdownToHtmlConverter,
        private PostReadTimeCalculator $postReadTimeCalculator,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function parse(string $contents, string $slug): array
    {
        $document = $this->frontMatterParser->parse($contents);
        $frontmatter = $document->frontMatter;
        $body = $document->body;

        if (! isset($frontmatter['slug'])) {
            $frontmatter['slug'] = $slug;
        }

        $content = $this->highlightedPostMarkdownToHtmlConverter->convert($body);

        return array_merge($frontmatter, [
            'body' => $body,
            'content' => $content,
            'excerpt' => $frontmatter['excerpt'] ?? $this->highlightedPostMarkdownToHtmlConverter->excerpt($body),
            'read_time_minutes' => $this->postReadTimeCalculator->calculateMinutes($content),
        ]);
    }
}
