<?php

declare(strict_types=1);

namespace App\Pages\Repositories;

use App\Pages\Page;
use App\Pages\PageSorter;

final readonly class PageGetRepository
{
    public function __construct(
        private PageSource $pageSource,
        private PageSorter $pageSorter,
    ) {}

    /**
     * @return array<int, Page>
     */
    public function getAllExcludingDrafts(): array
    {
        return $this->pageSorter->sortByTitle($this->pageSource->allExcludingDrafts());
    }

    /**
     * @return array<int, Page>
     */
    public function getAllIncludingDrafts(): array
    {
        return $this->pageSorter->sortByTitle($this->pageSource->all());
    }

    public function find(string $slug): ?Page
    {
        return $this->pageSource->find($slug);
    }

    public function exists(string $slug): bool
    {
        return $this->pageSource->exists($slug);
    }
}
