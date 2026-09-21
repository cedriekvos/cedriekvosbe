<?php

declare(strict_types=1);

namespace App\Pages;

final readonly class Page
{
    public function __construct(
        public string $slug,
        public string $title,
        public string $body,
        public string $content,
        public bool $is_draft = false,
    ) {}
}
