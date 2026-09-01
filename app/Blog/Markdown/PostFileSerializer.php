<?php

declare(strict_types=1);

namespace App\Blog\Markdown;

use App\Markdown\FrontMatterSerializer;

final readonly class PostFileSerializer
{
    public function __construct(
        private FrontMatterSerializer $frontMatterSerializer,
    ) {}

    /**
     * Serialise front-matter and body into the on-disk Markdown format. Null or
     * empty front-matter values are omitted.
     *
     * @param  array{title: string, date: string, excerpt?: ?string, featured?: bool}  $attrs
     */
    public function serialize(string $slug, array $attrs, string $body): string
    {
        $frontmatter = array_filter([
            'slug' => $slug,
            'title' => $attrs['title'],
            'date' => $attrs['date'],
            'excerpt' => $attrs['excerpt'] ?? null,
            'featured' => ($attrs['featured'] ?? false) ? true : null,
        ], fn (mixed $value): bool => $value !== null && $value !== '');

        return $this->frontMatterSerializer->serialize($frontmatter, $body);
    }
}
