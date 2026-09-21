<?php

declare(strict_types=1);

namespace App\Pages\Markdown;

use App\Markdown\FrontMatterParser;

final readonly class PageFileParser
{
    public function __construct(
        private FrontMatterParser $frontMatterParser,
        private PageMarkdownToHtmlConverter $pageMarkdownToHtmlConverter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function parse(string $contents, string $slug): array
    {
        $document = $this->frontMatterParser->parse($contents);
        $frontmatter = $document->frontMatter;
        $body = $document->body;

        $frontmatter['slug'] ??= $slug;

        return array_merge($frontmatter, [
            'body' => $body,
            'content' => $this->pageMarkdownToHtmlConverter->convert($body),
        ]);
    }
}
