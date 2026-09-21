<?php

declare(strict_types=1);

namespace App\Pages;

final readonly class PageSorter
{
    /**
     * Pages have no date field, so the admin list orders them alphabetically
     * by title (A→Z).
     *
     * @param  array<int, Page>  $pages
     * @return array<int, Page>
     */
    public function sortByTitle(array $pages): array
    {
        usort($pages, fn (Page $a, Page $b): int => $a->title <=> $b->title);

        return $pages;
    }
}
