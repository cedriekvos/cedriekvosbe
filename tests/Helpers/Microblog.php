<?php

use App\Livewire\Admin\MessageIndex;
use App\Microblog\Message;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Build a Message DTO with sensible defaults; override only the fields a test cares about.
 */
function makeMessage(
    string $id = 'id',
    string $body = 'body',
    string $bodyAsHtml = '<p>body</p>',
    string $postedAt = '2026-01-01 00:00:00',
): Message {
    return new Message($id, $body, $bodyAsHtml, $postedAt);
}

/**
 * Isolate the microblog domain's storage for each test (ADR 0002: flat files
 * on a dedicated `microblog` disk, mirroring how `posts`/`meta` are faked).
 */
function usesFakeMicroblogRepository(): void
{
    beforeEach(function () {
        Storage::fake('microblog');
    });
}

/**
 * Write a message directly onto the `microblog` disk as `{id}.md` with `id`
 * and `posted_at` front matter (ADR 0002's file shape), bypassing the not-yet-built
 * write repository so read-side scenarios can seed fixtures independently of it.
 */
function writeMessageFile(string $id, string $body, string $postedAt): void
{
    Storage::disk('microblog')->put($id.'.md', "---\nid: {$id}\nposted_at: '{$postedAt}'\n---\n\n{$body}\n");
}

/**
 * Seed a single message with a generated ULID id, defaulting posted_at to now.
 * Returns the generated id so callers can reference it (e.g. for edit links).
 */
function postMessage(string $body, ?string $postedAt = null): string
{
    $id = (string) Str::ulid();

    writeMessageFile($id, $body, $postedAt ?? Carbon::now()->format('Y-m-d H:i:s'));

    return $id;
}

/**
 * Seed messages with strictly increasing posted_at timestamps, one minute apart,
 * so newest-first listings render in the reverse of `$bodies`' (oldest-first) order.
 *
 * @param  array<int, string>  $bodies
 * @return array<string, string> message id keyed by body
 */
function postMessagesInOrder(array $bodies): array
{
    $postedAt = Carbon::create(2026, 1, 1, 0, 0, 0);
    $ids = [];

    foreach ($bodies as $body) {
        $ids[$body] = postMessage($body, $postedAt->format('Y-m-d H:i:s'));
        $postedAt = $postedAt->addMinute();
    }

    return $ids;
}

/**
 * Seed a single message, render the homepage, and return the HTML of its
 * `data-section="microblog"` block — the fragment the frontend link scenarios
 * assert on, scoped so that markup elsewhere on the page cannot satisfy them.
 */
function renderedMessageOnHomepage(string $body): string
{
    postMessage($body);

    $html = (string) test()->get('/')->assertSuccessful()->getContent();

    return homepageSectionHtml($html, 'microblog');
}

/**
 * Seed a single message and return the rendered HTML of the admin message list.
 */
function renderedMessageInAdminList(string $body): string
{
    postMessage($body);

    return Livewire::test(MessageIndex::class)->html();
}
