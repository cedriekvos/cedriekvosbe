<?php

declare(strict_types=1);

namespace App\Microblog;

final readonly class Message
{
    public function __construct(
        public string $id,
        public string $body,
        public string $body_as_html,
        public string $posted_at,
    ) {}
}
