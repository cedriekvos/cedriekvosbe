<?php

declare(strict_types=1);

namespace App\Scratchpad;

final readonly class Scratchpad
{
    public function __construct(
        public string $content,
    ) {}
}
