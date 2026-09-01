<?php

declare(strict_types=1);

namespace App\Blog\Repositories;

use App\Blog\Markdown\PostFileSerializer;
use App\Blog\Storage\PostFileStorage;
use RuntimeException;

final readonly class PostWriteRepository
{
    public function __construct(
        private PostFileStorage $postFileStorage,
        private PostFileSerializer $postFileSerializer,
    ) {}

    /**
     * Create a new post on disk. Returns the final slug (with draft- prefix when draft).
     *
     * @param  array{title: string, date: string, excerpt?: ?string, featured?: bool}  $attrs
     */
    public function create(array $attrs, string $body, string $baseSlug, bool $isDraft): string
    {
        $finalSlug = $this->postFileStorage->getSlugFor($baseSlug, $isDraft);

        if ($this->postFileStorage->exists($finalSlug)) {
            throw new RuntimeException("A post with slug [{$finalSlug}] already exists.");
        }

        $this->writeFile($finalSlug, $attrs, $body);

        return $finalSlug;
    }

    /**
     * Update an existing post. Renames the file when the slug or draft flag changes.
     * Write-then-delete is atomic-safe: the old file is only removed after the new file exists.
     *
     * @param  array{title: string, date: string, excerpt?: ?string, featured?: bool}  $attrs
     */
    public function update(string $originalSlug, array $attrs, string $body, string $baseSlug, bool $isDraft): string
    {
        if (! $this->postFileStorage->exists($originalSlug)) {
            throw new RuntimeException("Post [{$originalSlug}] does not exist.");
        }

        $finalSlug = $this->postFileStorage->getSlugFor($baseSlug, $isDraft);

        if ($finalSlug !== $originalSlug && $this->postFileStorage->exists($finalSlug)) {
            throw new RuntimeException("A post with slug [{$finalSlug}] already exists.");
        }

        $this->writeFile($finalSlug, $attrs, $body);

        if ($finalSlug !== $originalSlug) {
            $this->postFileStorage->delete($originalSlug);
        }

        return $finalSlug;
    }

    public function delete(string $slug): bool
    {
        return $this->postFileStorage->delete($slug);
    }

    /**
     * @param  array{title: string, date: string, excerpt?: ?string, featured?: bool}  $attrs
     */
    private function writeFile(string $slug, array $attrs, string $body): void
    {
        $this->postFileStorage->put($slug, $this->postFileSerializer->serialize($slug, $attrs, $body));
    }
}
