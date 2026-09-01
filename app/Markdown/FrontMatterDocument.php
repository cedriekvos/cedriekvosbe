<?php

declare(strict_types=1);

namespace App\Markdown;

final readonly class FrontMatterDocument
{
    /**
     * @param  array<string, mixed>  $frontMatter
     */
    public function __construct(
        public array $frontMatter,
        public string $body,
    ) {}
}
