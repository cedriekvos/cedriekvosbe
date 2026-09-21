<?php

declare(strict_types=1);

namespace App\Pages\Markdown;

use App\Markdown\FrontMatterSerializer;

final readonly class PageFileSerializer
{
    public function __construct(
        private FrontMatterSerializer $frontMatterSerializer,
    ) {}

    /**
     * Serialise front-matter and body into the on-disk Markdown format.
     *
     * @param  array{title: string}  $attrs
     */
    public function serialize(string $slug, array $attrs, string $body): string
    {
        $frontmatter = [
            'slug' => $slug,
            'title' => $attrs['title'],
        ];

        return $this->frontMatterSerializer->serialize($frontmatter, $body);
    }
}
