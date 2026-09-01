<?php

declare(strict_types=1);

namespace App\About;

final readonly class About
{
    public function __construct(
        public string $heading,
        public string $bio_as_markdown,
        public string $bio_as_html,
        public bool $is_visible,
    ) {}
}
