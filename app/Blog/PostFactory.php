<?php

declare(strict_types=1);

namespace App\Blog;

final readonly class PostFactory
{
    /**
     * Build a Post from parsed front-matter/body data. Missing or non-string
     * fields default to an empty string; a missing or non-int read time
     * defaults to the minimum of 1 minute.
     *
     * @param  array<string, mixed>  $data
     */
    public function make(array $data, bool $isDraft): Post
    {
        return new Post(
            slug: $this->stringOrEmpty($data['slug'] ?? null),
            title: $this->stringOrEmpty($data['title'] ?? null),
            date: $this->stringOrEmpty($data['date'] ?? null),
            excerpt: $this->stringOrEmpty($data['excerpt'] ?? null),
            body: $this->stringOrEmpty($data['body'] ?? null),
            content: $this->stringOrEmpty($data['content'] ?? null),
            read_time_minutes: $this->intOrMinimum($data['read_time_minutes'] ?? null),
            is_draft: $isDraft,
            is_featured: $this->boolOrFalse($data['featured'] ?? null),
        );
    }

    private function stringOrEmpty(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    private function intOrMinimum(mixed $value): int
    {
        return is_int($value) ? $value : 1;
    }

    private function boolOrFalse(mixed $value): bool
    {
        return is_bool($value) && $value;
    }
}
