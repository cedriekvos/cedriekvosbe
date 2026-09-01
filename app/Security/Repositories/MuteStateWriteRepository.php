<?php

declare(strict_types=1);

namespace App\Security\Repositories;

use App\Security\Json\MuteStateSerializer;
use App\Security\Storage\MuteFileStorage;

final readonly class MuteStateWriteRepository
{
    public function __construct(
        private MuteFileStorage $muteFileStorage,
        private MuteStateSerializer $muteStateSerializer,
    ) {}

    /**
     * @param  array<string, string>  $state
     */
    public function save(array $state): void
    {
        $this->muteFileStorage->write($this->muteStateSerializer->serialize($state));
    }
}
