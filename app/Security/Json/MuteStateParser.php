<?php

declare(strict_types=1);

namespace App\Security\Json;

final readonly class MuteStateParser
{
    /**
     * Decode the stored mute map (advisory|package => ISO-8601 timestamp).
     * Malformed or empty content yields an empty map.
     *
     * @return array<string, string>
     */
    public function parse(string $json): array
    {
        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            return [];
        }

        $state = [];

        foreach ($decoded as $key => $timestamp) {
            if (is_string($key) && is_string($timestamp)) {
                $state[$key] = $timestamp;
            }
        }

        return $state;
    }
}
