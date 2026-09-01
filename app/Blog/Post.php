<?php

declare(strict_types=1);

namespace App\Blog;

final readonly class Post
{
    public function __construct(
        public string $slug,
        public string $title,
        public string $date,
        public string $excerpt,
        public string $body,
        public string $content,
        public int $read_time_minutes,
        public bool $is_draft = false,
        public bool $is_featured = false,
    ) {}
}
