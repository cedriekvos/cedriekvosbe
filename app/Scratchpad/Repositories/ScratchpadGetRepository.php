<?php

declare(strict_types=1);

namespace App\Scratchpad\Repositories;

use App\Scratchpad\Scratchpad;
use App\Scratchpad\Storage\ScratchpadFileStorage;

final readonly class ScratchpadGetRepository
{
    public function __construct(
        private ScratchpadFileStorage $scratchpadFileStorage,
    ) {}

    public function get(): Scratchpad
    {
        return new Scratchpad($this->scratchpadFileStorage->read());
    }
}
