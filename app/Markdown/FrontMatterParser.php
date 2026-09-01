<?php

declare(strict_types=1);

namespace App\Markdown;

use Symfony\Component\Yaml\Yaml;

final readonly class FrontMatterParser
{
    private const string FENCE = '---';

    /**
     * Split a Markdown document into its YAML front matter and its body. A
     * document without an opening fence, or with an unterminated one, yields
     * empty front matter and the untouched contents as body.
     *
     * The fences are matched line by line rather than as raw substrings, so a
     * hand-edited file whose closing fence is the last line without a trailing
     * newline — and an empty `---`/`---` block — still parse.
     */
    public function parse(string $contents): FrontMatterDocument
    {
        $lines = explode("\n", $contents);

        if (array_shift($lines) !== self::FENCE) {
            return new FrontMatterDocument([], $contents);
        }

        $closingLine = array_search(self::FENCE, $lines, true);

        if ($closingLine === false) {
            return new FrontMatterDocument([], $contents);
        }

        $parsed = Yaml::parse(implode("\n", array_slice($lines, 0, $closingLine)));

        return new FrontMatterDocument(
            $this->mapOrEmpty($parsed),
            ltrim(implode("\n", array_slice($lines, $closingLine + 1)), "\n"),
        );
    }

    /**
     * Front matter that is not a YAML map — a bare scalar, say — carries no
     * usable keys, so it is treated as absent.
     *
     * @return array<string, mixed>
     */
    private function mapOrEmpty(mixed $parsed): array
    {
        if (! is_array($parsed)) {
            return [];
        }

        /** @var array<string, mixed> $parsed */
        return $parsed;
    }
}
