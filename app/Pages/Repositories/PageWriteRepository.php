<?php

declare(strict_types=1);

namespace App\Pages\Repositories;

use App\Pages\Markdown\PageFileSerializer;
use App\Pages\Storage\PageFileStorage;
use RuntimeException;

final readonly class PageWriteRepository
{
    public function __construct(
        private PageFileStorage $pageFileStorage,
        private PageFileSerializer $pageFileSerializer,
    ) {}

    /**
     * Create a new page on disk. Returns the final slug (with draft- prefix when draft).
     *
     * @param  array{title: string}  $attrs
     */
    public function create(array $attrs, string $body, string $baseSlug, bool $isDraft): string
    {
        $finalSlug = $this->pageFileStorage->getSlugFor($baseSlug, $isDraft);

        if ($this->pageFileStorage->exists($finalSlug)) {
            throw new RuntimeException("A page with slug [{$finalSlug}] already exists.");
        }

        $this->writeFile($finalSlug, $attrs, $body);

        return $finalSlug;
    }

    /**
     * Update an existing page. Renames the file when the slug or draft flag changes.
     * Write-then-delete is atomic-safe: the old file is only removed after the new file exists.
     *
     * @param  array{title: string}  $attrs
     */
    public function update(string $originalSlug, array $attrs, string $body, string $baseSlug, bool $isDraft): string
    {
        if (! $this->pageFileStorage->exists($originalSlug)) {
            throw new RuntimeException("Page [{$originalSlug}] does not exist.");
        }

        $finalSlug = $this->pageFileStorage->getSlugFor($baseSlug, $isDraft);

        if ($finalSlug !== $originalSlug && $this->pageFileStorage->exists($finalSlug)) {
            throw new RuntimeException("A page with slug [{$finalSlug}] already exists.");
        }

        $this->writeFile($finalSlug, $attrs, $body);

        if ($finalSlug !== $originalSlug) {
            $this->pageFileStorage->delete($originalSlug);
        }

        return $finalSlug;
    }

    public function delete(string $slug): bool
    {
        return $this->pageFileStorage->delete($slug);
    }

    /**
     * @param  array{title: string}  $attrs
     */
    private function writeFile(string $slug, array $attrs, string $body): void
    {
        $this->pageFileStorage->put($slug, $this->pageFileSerializer->serialize($slug, $attrs, $body));
    }
}
