<?php

declare(strict_types=1);

namespace App\Microblog\Markdown;

use App\Markdown\FrontMatterSerializer;

final readonly class MessageFileSerializer
{
    public function __construct(
        private FrontMatterSerializer $frontMatterSerializer,
    ) {}

    /**
     * Serialise front-matter and body into the on-disk Markdown format.
     */
    public function serialize(string $id, string $postedAt, string $body): string
    {
        $frontmatter = [
            'id' => $id,
            'posted_at' => $postedAt,
        ];

        return $this->frontMatterSerializer->serialize($frontmatter, $body);
    }
}
