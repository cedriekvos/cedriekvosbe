<?php

declare(strict_types=1);

namespace App\Security\Json;

final readonly class MuteStateSerializer
{
    /**
     * Encode the mute map (advisory|package => ISO-8601 timestamp) for storage.
     *
     * @param  array<string, string>  $state
     */
    public function serialize(array $state): string
    {
        return json_encode($state, JSON_THROW_ON_ERROR);
    }
}
