<?php

declare(strict_types=1);

namespace App\Microblog\Markdown;

use App\Markdown\FrontMatterParser;

final readonly class MessageFileParser
{
    public function __construct(
        private FrontMatterParser $frontMatterParser,
    ) {}

    /**
     * Parse a message file into its front matter and body.
     *
     * The trailing newline the file format ends on is stripped, mirroring the
     * `trim(…, "\n")` MessageFileSerializer applies on the way in: a message is
     * a single short text, not a document, and that newline would otherwise
     * surface in the composer and count against the 280 character limit.
     *
     * @return array<string, mixed>
     */
    public function parse(string $contents, string $id): array
    {
        $document = $this->frontMatterParser->parse($contents);
        $frontmatter = $document->frontMatter;

        $frontmatter['id'] ??= $id;

        return array_merge($frontmatter, ['body' => rtrim($document->body, "\n")]);
    }
}
