<?php

declare(strict_types=1);

namespace App\Blog\Repositories;

use App\Blog\Post;

final readonly class PostsRepository
{
    public function __construct(
        private PostGetRepository $postGetRepository,
        private PostWriteRepository $postWriteRepository,
    ) {}

    /**
     * Published posts only, sorted by date descending.
     *
     * @return array<int, Post>
     */
    public function allExcludeDrafts(): array
    {
        return $this->postGetRepository->getAllExcludingDrafts();
    }

    /**
     * Every post — published and drafts — sorted by date descending.
     *
     * @return array<int, Post>
     */
    public function allIncludeDrafts(): array
    {
        return $this->postGetRepository->getAllIncludingDrafts();
    }

    public function find(string $slug): ?Post
    {
        return $this->postGetRepository->find($slug);
    }

    public function exists(string $slug): bool
    {
        return $this->postGetRepository->exists($slug);
    }

    /**
     * Create a new post on disk. Returns the final slug (with draft- prefix when draft).
     *
     * @param  array{title: string, date: string, excerpt?: ?string, featured?: bool}  $attrs
     */
    public function create(array $attrs, string $body, string $baseSlug, bool $isDraft): string
    {
        return $this->postWriteRepository->create($attrs, $body, $baseSlug, $isDraft);
    }

    /**
     * Update an existing post. Returns the final slug (with draft- prefix when draft).
     *
     * @param  array{title: string, date: string, excerpt?: ?string, featured?: bool}  $attrs
     */
    public function update(string $originalSlug, array $attrs, string $body, string $baseSlug, bool $isDraft): string
    {
        return $this->postWriteRepository->update($originalSlug, $attrs, $body, $baseSlug, $isDraft);
    }

    public function delete(string $slug): bool
    {
        return $this->postWriteRepository->delete($slug);
    }
}
