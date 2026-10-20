<?php

use App\Blog\Post;
use Illuminate\Support\Facades\Storage;

/**
 * Build a Post DTO with sensible defaults; override only the fields a test cares about.
 */
function makePost(
    string $slug = 'slug',
    string $title = 'Title',
    string $date = '2026-01-01',
    string $excerpt = 'excerpt',
    string $body = 'body',
    string $content = '<p>body</p>',
    int $readTimeMinutes = 1,
    bool $isDraft = false,
): Post {
    return new Post($slug, $title, $date, $excerpt, $body, $content, $readTimeMinutes, $isDraft);
}

/**
 * Isolate the homepage's storage for each test so reads and writes never touch
 * real storage. The homepage reads posts from the `posts` disk and the about-me
 * section from the `meta` disk, so both are faked.
 */
function usesFakePostsRepository(): void
{
    beforeEach(function () {
        Storage::fake('posts');
        Storage::fake('meta');
    });
}

/**
 * Write a Markdown post onto the `posts` disk. Drafts use a `draft-` slug prefix.
 * The body defaults to `body` when omitted.
 */
function writePostFile(string $slug, string $title, string $date, ?string $excerpt = null, ?string $body = null, bool $isFeatured = false): void
{
    $frontmatter = "title: {$title}\ndate: '{$date}'\n";

    if ($excerpt !== null) {
        $frontmatter .= "excerpt: '{$excerpt}'\n";
    }

    if ($isFeatured) {
        $frontmatter .= "featured: true\n";
    }

    $body ??= 'body';

    Storage::disk('posts')->put($slug.'.md', "---\n{$frontmatter}---\n\n{$body}\n");
}

/**
 * Build a Markdown post body of exactly $wordCount space-separated words, for
 * read-time calculation tests.
 */
function wordsOfBody(int $wordCount): string
{
    return trim(str_repeat('word ', $wordCount));
}
