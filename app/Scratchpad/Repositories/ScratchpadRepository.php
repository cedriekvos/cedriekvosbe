<?php

declare(strict_types=1);

namespace App\Scratchpad\Repositories;

use App\Scratchpad\Scratchpad;

final readonly class ScratchpadRepository
{
    public function __construct(
        private ScratchpadGetRepository $scratchpadGetRepository,
        private ScratchpadWriteRepository $scratchpadWriteRepository,
    ) {}

    public function get(): Scratchpad
    {
        return $this->scratchpadGetRepository->get();
    }

    public function save(string $content): void
    {
        $this->scratchpadWriteRepository->save($content);
    }
}
