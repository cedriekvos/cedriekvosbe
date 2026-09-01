<?php

declare(strict_types=1);

namespace App\About\Markdown;

use Symfony\Component\Yaml\Yaml;

final readonly class AboutFileParser
{
    /**
     * Parse the on-disk YAML into a heading/bio map. Empty or non-map contents
     * yield an empty array.
     *
     * @return array<string, mixed>
     */
    public function parse(string $contents): array
    {
        $parsed = Yaml::parse($contents);

        if (! is_array($parsed)) {
            return [];
        }

        /** @var array<string, mixed> $parsed */
        return $parsed;
    }
}
