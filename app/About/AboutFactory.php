<?php

declare(strict_types=1);

namespace App\About;

use App\Blog\Markdown\PostMarkdownToHtmlConverter;

final readonly class AboutFactory
{
    public function __construct(
        private PostMarkdownToHtmlConverter $postMarkdownToHtmlConverter,
    ) {}

    /**
     * Build an About from parsed data. Missing or non-string fields default to
     * an empty string; the bio is rendered to HTML and the section is visible
     * whenever the heading or the bio has content.
     *
     * @param  array<string, mixed>  $data
     */
    public function make(array $data): About
    {
        $heading = $this->stringOrEmpty($data['heading'] ?? null);
        $bio = $this->stringOrEmpty($data['bio'] ?? null);

        return new About(
            heading: $heading,
            bio_as_markdown: $bio,
            bio_as_html: $this->postMarkdownToHtmlConverter->convert($bio),
            is_visible: $heading !== '' || $bio !== '',
        );
    }

    private function stringOrEmpty(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
