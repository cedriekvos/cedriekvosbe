<?php

declare(strict_types=1);

namespace App\Pages;

use App\Pages\Repositories\PageGetRepository;

/**
 * Finds a page by its clean slug whether it is published or still a draft, so
 * an editor can preview work that has no public URL yet.
 */
final readonly class PagePreviewFinder
{
    public function __construct(
        private PageGetRepository $pageGetRepository,
        private DraftSlug $draftSlug,
    ) {}

    public function find(string $slug): ?Page
    {
        return $this->pageGetRepository->find($slug)
            ?? $this->pageGetRepository->find($this->draftSlug->apply($slug, true));
    }
}
