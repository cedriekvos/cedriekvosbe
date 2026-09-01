<?php

declare(strict_types=1);

namespace App\About\Markdown;

use Symfony\Component\Yaml\Yaml;

final readonly class AboutFileSerializer
{
    /**
     * Serialise the about-me heading and bio into the on-disk YAML format.
     */
    public function serialize(string $heading, string $bio): string
    {
        return Yaml::dump(['heading' => $heading, 'bio' => $bio]);
    }
}
