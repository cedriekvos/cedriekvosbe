<?php

declare(strict_types=1);

namespace App\Blog;

use Illuminate\Support\Str;

final readonly class PostReadTimeCalculator
{
    /**
     * Estimate the minutes it takes to read rendered HTML content, based on its
     * visible text (tags and attribute values such as image alt text excluded),
     * assuming 200 words per minute.
     */
    public function calculateMinutes(string $content): int
    {
        return (int) ceil($this->countWords($content) / 200);
    }

    /**
     * explode() always returns at least one element, even for empty text, so the
     * word count here is never less than 1 — which is exactly the minimum read
     * time the spec requires, with no extra flooring logic needed.
     */
    private function countWords(string $content): int
    {
        $text = Str::of(strip_tags($content))->replaceMatches('/\s+/', ' ')->trim()->value();

        return count(explode(' ', $text));
    }
}
