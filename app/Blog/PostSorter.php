<?php

declare(strict_types=1);

namespace App\Blog;

final readonly class PostSorter
{
    /**
     * @param  array<int, Post>  $posts
     * @return array<int, Post>
     */
    public function sortByDateDescending(array $posts): array
    {
        usort($posts, $this->compareNewestFirst(...));

        return $posts;
    }

    /**
     * Compare two posts for a newest-first ordering.
     *
     * Posts are ranked by date (most recent first). When two posts share
     * the same date, the tie is broken by title in ascending (A→Z) order.
     *
     * @return int negative when $a comes first, positive when $b comes first, 0 when equal
     */
    private function compareNewestFirst(Post $a, Post $b): int
    {
        $timestampA = strtotime($a->date) ?: 0;
        $timestampB = strtotime($b->date) ?: 0;

        if ($timestampA !== $timestampB) {
            return $timestampB <=> $timestampA;
        }

        // $a and $b have the same date so fall back to an alphabetical (A→Z) comparison of titles.
        return $a->title <=> $b->title;
    }
}
