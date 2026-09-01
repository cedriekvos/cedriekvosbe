<?php

declare(strict_types=1);

namespace App\Markdown;

use Symfony\Component\Yaml\Yaml;

final readonly class FrontMatterSerializer
{
    private const string FENCE = "---\n";

    /**
     * Serialise front matter and body into the on-disk Markdown format. The
     * body is CRLF-normalised and stripped of surrounding blank lines so
     * repeated saves stay byte-stable.
     *
     * @param  array<string, mixed>  $frontMatter
     */
    public function serialize(array $frontMatter, string $body): string
    {
        $normalizedBody = trim(str_replace("\r\n", "\n", $body), "\n");

        return self::FENCE.Yaml::dump($frontMatter).self::FENCE."\n".$normalizedBody."\n";
    }
}
