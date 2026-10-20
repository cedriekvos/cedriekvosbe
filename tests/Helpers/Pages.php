<?php

use App\Pages\Page;
use Illuminate\Support\Facades\Storage;

/**
 * Build a Page DTO with sensible defaults; override only the fields a test cares about.
 */
function makePage(
    string $slug = 'slug',
    string $title = 'Title',
    string $body = 'body',
    string $content = '<p>body</p>',
    bool $isDraft = false,
): Page {
    return new Page($slug, $title, $body, $content, $isDraft);
}

/**
 * Isolate the pages domain's storage for each test so reads and writes never touch
 * real storage. Mirrors usesFakePostsRepository() for the `pages` disk.
 */
function usesFakePagesRepository(): void
{
    beforeEach(function () {
        Storage::fake('pages');
    });
}

/**
 * Write a Markdown page onto the `pages` disk. Drafts use a `draft-` slug prefix,
 * the same convention as writePostFile(). The body defaults to `body` when omitted.
 */
function writePageFile(string $slug, string $title, ?string $body = null): void
{
    $body ??= 'body';

    Storage::disk('pages')->put($slug.'.md', "---\ntitle: {$title}\n---\n\n{$body}\n");
}
