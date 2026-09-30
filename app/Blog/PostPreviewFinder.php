<?php

declare(strict_types=1);

namespace App\Blog;

use App\Blog\Repositories\PostGetRepository;

/**
 * Finds a post by its clean slug whether it is published or still a draft, so
 * an editor can preview work that has no public URL yet.
 */
final readonly class PostPreviewFinder
{
    public function __construct(
        private PostGetRepository $postGetRepository,
        private DraftSlug $draftSlug,
    ) {}

    public function find(string $slug): ?Post
    {
        return $this->postGetRepository->find($slug)
            ?? $this->postGetRepository->find($this->draftSlug->apply($slug, true));
    }
}
