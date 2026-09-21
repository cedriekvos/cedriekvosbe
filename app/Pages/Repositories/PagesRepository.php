<?php

declare(strict_types=1);

namespace App\Pages\Repositories;

use App\Pages\Page;

final readonly class PagesRepository
{
    public function __construct(
        private PageGetRepository $pageGetRepository,
        private PageWriteRepository $pageWriteRepository,
    ) {}

    /**
     * Published pages only, sorted by title.
     *
     * @return array<int, Page>
     */
    public function allExcludeDrafts(): array
    {
        return $this->pageGetRepository->getAllExcludingDrafts();
    }

    /**
     * Every page — published and drafts — sorted by title.
     *
     * @return array<int, Page>
     */
    public function allIncludeDrafts(): array
    {
        return $this->pageGetRepository->getAllIncludingDrafts();
    }

    public function find(string $slug): ?Page
    {
        return $this->pageGetRepository->find($slug);
    }

    public function exists(string $slug): bool
    {
        return $this->pageGetRepository->exists($slug);
    }

    /**
     * Create a new page on disk. Returns the final slug (with draft- prefix when draft).
     *
     * @param  array{title: string}  $attrs
     */
    public function create(array $attrs, string $body, string $baseSlug, bool $isDraft): string
    {
        return $this->pageWriteRepository->create($attrs, $body, $baseSlug, $isDraft);
    }

    /**
     * Update an existing page. Returns the final slug (with draft- prefix when draft).
     *
     * @param  array{title: string}  $attrs
     */
    public function update(string $originalSlug, array $attrs, string $body, string $baseSlug, bool $isDraft): string
    {
        return $this->pageWriteRepository->update($originalSlug, $attrs, $body, $baseSlug, $isDraft);
    }

    public function delete(string $slug): bool
    {
        return $this->pageWriteRepository->delete($slug);
    }
}
