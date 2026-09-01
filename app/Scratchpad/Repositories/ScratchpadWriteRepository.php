<?php

declare(strict_types=1);

namespace App\Scratchpad\Repositories;

use App\Scratchpad\Storage\ScratchpadFileStorage;

final readonly class ScratchpadWriteRepository
{
    public function __construct(
        private ScratchpadFileStorage $scratchpadFileStorage,
    ) {}

    public function save(string $content): void
    {
        $this->scratchpadFileStorage->write($content);
    }
}
