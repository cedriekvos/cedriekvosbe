<?php

declare(strict_types=1);

namespace App\Pages\Repositories;

use App\Pages\Markdown\PageFileParser;
use App\Pages\Page;
use App\Pages\PageFactory;
use App\Pages\PageFilter;
use App\Pages\Storage\PageFileStorage;

final readonly class PageSource
{
    public function __construct(
        private PageFileStorage $pageFileStorage,
        private PageFileParser $pageFileParser,
        private PageFactory $pageFactory,
        private PageFilter $pageFilter,
    ) {}

    /**
     * @return array<int, Page>
     */
    public function all(): array
    {
        return array_map($this->toPage(...), $this->pageFileStorage->all());
    }

    /**
     * Published pages only. Drafts are dropped by slug before any file is read,
     * so rendering cost scales with what is published rather than with what is
     * on disk.
     *
     * @return array<int, Page>
     */
    public function allExcludingDrafts(): array
    {
        return array_map($this->toPage(...), $this->pageFilter->excludeDrafts($this->pageFileStorage->all()));
    }

    public function find(string $slug): ?Page
    {
        return $this->pageFileStorage->exists($slug) ? $this->toPage($slug) : null;
    }

    public function exists(string $slug): bool
    {
        return $this->pageFileStorage->exists($slug);
    }

    private function toPage(string $slug): Page
    {
        $data = $this->pageFileParser->parse($this->pageFileStorage->read($slug), $slug);

        return $this->pageFactory->make($data, $this->pageFileStorage->startsWithDraft($slug));
    }
}
