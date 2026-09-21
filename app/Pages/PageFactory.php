<?php

declare(strict_types=1);

namespace App\Pages;

final readonly class PageFactory
{
    /**
     * Build a Page from parsed front-matter/body data. Missing or non-string
     * fields default to an empty string.
     *
     * @param  array<string, mixed>  $data
     */
    public function make(array $data, bool $isDraft): Page
    {
        return new Page(
            slug: $this->stringOrEmpty($data['slug'] ?? null),
            title: $this->stringOrEmpty($data['title'] ?? null),
            body: $this->stringOrEmpty($data['body'] ?? null),
            content: $this->stringOrEmpty($data['content'] ?? null),
            is_draft: $isDraft,
        );
    }

    private function stringOrEmpty(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
