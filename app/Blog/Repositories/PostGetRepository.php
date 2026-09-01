<?php

declare(strict_types=1);

namespace App\Blog\Repositories;

use App\Blog\Post;
use App\Blog\PostSorter;

final readonly class PostGetRepository
{
    public function __construct(
        private PostSource $postSource,
        private PostSorter $postSorter,
    ) {}

    /**
     * @return array<int, Post>
     */
    public function getAllExcludingDrafts(): array
    {
        return $this->postSorter->sortByDateDescending($this->postSource->allExcludingDrafts());
    }

    /**
     * @return array<int, Post>
     */
    public function getAllIncludingDrafts(): array
    {
        return $this->postSorter->sortByDateDescending($this->postSource->all());
    }

    public function find(string $slug): ?Post
    {
        return $this->postSource->find($slug);
    }

    public function exists(string $slug): bool
    {
        return $this->postSource->exists($slug);
    }
}
