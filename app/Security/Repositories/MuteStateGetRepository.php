<?php

declare(strict_types=1);

namespace App\Security\Repositories;

use App\Security\Json\MuteStateParser;
use App\Security\Storage\MuteFileStorage;

final readonly class MuteStateGetRepository
{
    public function __construct(
        private MuteFileStorage $muteFileStorage,
        private MuteStateParser $muteStateParser,
    ) {}

    /**
     * @return array<string, string>
     */
    public function get(): array
    {
        return $this->muteStateParser->parse($this->muteFileStorage->read());
    }
}
