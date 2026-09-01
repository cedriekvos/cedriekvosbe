<?php

declare(strict_types=1);

namespace App\Blog;

final readonly class PostFilter
{
    public function __construct(
        private DraftSlug $draftSlug,
    ) {}

    /**
     * Drop draft slugs before their files are read and rendered, so a public
     * request never pays to parse a post it will not show.
     *
     * @param  array<int, string>  $slugs
     * @return array<int, string>
     */
    public function excludeDrafts(array $slugs): array
    {
        return array_values(array_filter(
            $slugs,
            fn (string $slug): bool => ! $this->draftSlug->isDraft($slug),
        ));
    }
}
