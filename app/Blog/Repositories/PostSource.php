<?php

declare(strict_types=1);

namespace App\Blog\Repositories;

use App\Blog\Markdown\PostFileParser;
use App\Blog\Post;
use App\Blog\PostFactory;
use App\Blog\PostFilter;
use App\Blog\Storage\PostFileStorage;

final readonly class PostSource
{
    public function __construct(
        private PostFileStorage $postFileStorage,
        private PostFileParser $postFileParser,
        private PostFactory $postFactory,
        private PostFilter $postFilter,
    ) {}

    /**
     * @return array<int, Post>
     */
    public function all(): array
    {
        return array_map($this->toPost(...), $this->postFileStorage->all());
    }

    /**
     * Published posts only. Drafts are dropped by slug before any file is read,
     * so rendering cost scales with what is published rather than with what is
     * on disk.
     *
     * @return array<int, Post>
     */
    public function allExcludingDrafts(): array
    {
        return array_map($this->toPost(...), $this->postFilter->excludeDrafts($this->postFileStorage->all()));
    }

    public function find(string $slug): ?Post
    {
        return $this->postFileStorage->exists($slug) ? $this->toPost($slug) : null;
    }

    public function exists(string $slug): bool
    {
        return $this->postFileStorage->exists($slug);
    }

    private function toPost(string $slug): Post
    {
        $data = $this->postFileParser->parse($this->postFileStorage->read($slug), $slug);

        return $this->postFactory->make($data, $this->postFileStorage->startsWithDraft($slug));
    }
}
