<?php

declare(strict_types=1);

namespace App\Pages;

use Illuminate\Support\Str;

/**
 * Owns the `draft-` slug prefix convention: a page is a draft when — and only
 * when — its stored slug carries the prefix. Mirrors App\Blog\DraftSlug.
 */
final readonly class DraftSlug
{
    public const string PREFIX = 'draft-';

    public function isDraft(string $slug): bool
    {
        return Str::startsWith($slug, self::PREFIX);
    }

    /**
     * The stored slug for a base slug: prefixed when the page is a draft.
     */
    public function apply(string $baseSlug, bool $isDraft): string
    {
        return $isDraft ? self::PREFIX.$baseSlug : $baseSlug;
    }

    /**
     * The base slug behind a stored slug, with the draft prefix removed.
     */
    public function strip(string $slug): string
    {
        return $this->isDraft($slug) ? substr($slug, strlen(self::PREFIX)) : $slug;
    }
}
