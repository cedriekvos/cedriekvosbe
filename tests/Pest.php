<?php

use App\About\About;
use App\About\Repositories\AboutRepository;
use App\Blog\DraftSlug;
use App\Blog\Markdown\FencedCodeHighlightExtension;
use App\Blog\Markdown\HighlightedPostMarkdownToHtmlConverter;
use App\Blog\Markdown\PostFileParser;
use App\Blog\Post;
use App\Blog\PostFactory;
use App\Blog\PostFilter;
use App\Blog\PostReadTimeCalculator;
use App\Blog\PostSorter;
use App\Blog\Repositories\PostGetRepository;
use App\Blog\Repositories\PostSource;
use App\Blog\Storage\PostFileStorage;
use App\Livewire\Admin\MessageIndex;
use App\Markdown\FrontMatterParser;
use App\Microblog\Markdown\MessageTextToHtmlConverter;
use App\Microblog\Markdown\PlainTextRenderingExtension;
use App\Microblog\Markdown\WebUrlAutolinkExtension;
use App\Microblog\Message;
use App\Microblog\MessageFactory;
use App\Models\User;
use App\Scratchpad\Repositories\ScratchpadRepository;
use App\Scratchpad\Scratchpad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use Livewire\Livewire;
use Pest\Browser\Api\AwaitableWebpage;
use Pest\Browser\Api\PendingAwaitablePage;
use Tempest\Highlight\Highlighter;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit', 'Browser');

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
 * Build the real message body renderer: plain text with bare web URLs linked.
 */
function messageTextToHtmlConverter(): MessageTextToHtmlConverter
{
    return new MessageTextToHtmlConverter(
        new PlainTextRenderingExtension,
        new WebUrlAutolinkExtension,
        new ExternalLinkExtension,
    );
}

/**
 * Build a real MessageFactory with its body renderer wired in.
 */
function messageFactory(): MessageFactory
{
    return new MessageFactory(messageTextToHtmlConverter());
}

/**
 * Build a real PostGetRepository reading from the (faked) `posts` disk.
 */
function postGetRepository(): PostGetRepository
{
    $draftSlug = new DraftSlug;

    return new PostGetRepository(
        new PostSource(new PostFileStorage($draftSlug), new PostFileParser(new FrontMatterParser, new HighlightedPostMarkdownToHtmlConverter(new CommonMarkCoreExtension, new ExternalLinkExtension, new FencedCodeHighlightExtension(new Highlighter)), new PostReadTimeCalculator), new PostFactory, new PostFilter($draftSlug)),
        new PostSorter,
    );
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
 * Extract one post's card markup from a homepage response body, scoped between
 * its `/blog/{slug}` link and the next post link (or the end of the string),
 * so badge assertions can target a single post without matching markup that
 * happens to appear elsewhere on the page.
 */
function postCardHtml(string $body, string $slug): string
{
    $start = strpos($body, 'href="/blog/'.$slug.'"');

    if ($start === false) {
        return '';
    }

    $nextLinkPos = strpos($body, 'href="/blog/', $start + 1);

    return $nextLinkPos === false ? substr($body, $start) : substr($body, $start, $nextLinkPos - $start);
}

/**
 * Build a Markdown post body of exactly $wordCount space-separated words, for
 * read-time calculation tests.
 */
function wordsOfBody(int $wordCount): string
{
    return trim(str_repeat('word ', $wordCount));
}

/**
 * Configure the homepage about-me section's singleton content, through the same
 * `AboutRepository` the admin form saves with, so a scenario seeds it exactly as
 * the editor would. Persisted as about.yaml on the (faked) `meta` disk.
 */
function configureAboutMe(string $heading = '', string $bio = ''): void
{
    app(AboutRepository::class)->save($heading, $bio);
}

/**
 * Read the about-me singleton back from storage as an About DTO — the read
 * side of configureAboutMe(), used to assert what the admin editor persisted.
 */
function storedAbout(): About
{
    return app(AboutRepository::class)->get();
}

/**
 * Configure the admin scratchpad's singleton content, through the same
 * `ScratchpadRepository` the admin form saves with. Persisted as scratchpad.md on
 * the (faked) `meta` disk, alongside the about-me content.
 */
function configureScratchpad(string $content = ''): void
{
    app(ScratchpadRepository::class)->save($content);
}

/**
 * Read the scratchpad singleton back from storage as a Scratchpad DTO — the
 * read side of configureScratchpad(), used to assert what the admin editor persisted.
 */
function storedScratchpad(): Scratchpad
{
    return app(ScratchpadRepository::class)->get();
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
 * Sign in as a freshly created editor account before each test
 * (the admin features' "Given I am signed in as an editor" Background step).
 */
function signsInAsEditor(): void
{
    beforeEach(function () {
        $this->actingAs(User::factory()->create());
    });
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
 * The visible text of an HTML fragment: tags removed, entities decoded and runs
 * of whitespace collapsed, so a scenario can assert what a reader actually sees
 * rather than how it happens to be marked up.
 */
function htmlText(string $html): string
{
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);

    return Str::of($text)->replaceMatches('/\s+/', ' ')->trim()->value();
}

/**
 * The visible text of an HTML fragment with every `<a>` element removed, so a
 * scenario can assert that a phrase sits outside of any link.
 */
function htmlTextOutsideLinks(string $html): string
{
    return htmlText((string) preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $html));
}

/**
 * Convert a hex colour (e.g. "#8839ef") to the "rgb(r, g, b)" string a
 * browser reports for it via getComputedStyle().
 */
function hexToRgb(string $hex): string
{
    [$red, $green, $blue] = sscanf($hex, '#%02x%02x%02x');

    return "rgb({$red}, {$green}, {$blue})";
}

/**
 * The attribute string of the public header's GitHub profile link (the `<a>`
 * pointing at https://github.com/cedriekvos), so navigation scenarios can
 * assert its target/rel/aria-label/class without matching other page links.
 * Empty when the header carries no such link.
 */
function headerGithubLinkAttributes(string $html): string
{
    preg_match('/<a\b([^>]*\bhref="https:\/\/github\.com\/cedriekvos"[^>]*)>/i', $html, $match);

    return $match[1] ?? '';
}

/**
 * Read a double-quoted HTML attribute out of an element's attribute string,
 * returning an empty string when the attribute is absent.
 */
function htmlAttribute(string $attributes, string $name): string
{
    preg_match('/\b'.preg_quote($name, '/').'="([^"]*)"/i', $attributes, $match);

    return $match[1] ?? '';
}

/**
 * Parse every `<a>` element in a fragment of HTML, in document order, into the
 * href, visible link text and the attributes the link scenarios assert on.
 *
 * @return array<int, array{href: string, text: string, target: string, rel: string}>
 */
function htmlLinks(string $html): array
{
    preg_match_all('/<a\b([^>]*)>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER);

    $links = [];

    foreach ($matches as $match) {
        $links[] = [
            'href' => htmlAttribute($match[1], 'href'),
            'text' => htmlText($match[2]),
            'target' => htmlAttribute($match[1], 'target'),
            'rel' => htmlAttribute($match[1], 'rel'),
        ];
    }

    return $links;
}

/**
 * Extract the HTML of a `<section data-section="{$name}">...</section>` block
 * (the existing `data-section="about-me"` convention in resources/views/home.blade.php),
 * so scenarios can assert content is scoped to that section rather than bleeding
 * into (or being confused with) another part of the page.
 */
function homepageSectionHtml(string $html, string $name): string
{
    $start = strpos($html, 'data-section="'.$name.'"');
    expect($start)->not->toBeFalse();

    $end = strpos($html, '</section>', (int) $start);
    expect($end)->not->toBeFalse();

    return substr($html, (int) $start, (int) $end - (int) $start);
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

/**
 * Isolate the composer vulnerability alert for each test: a faked `security` disk
 * for the 48h mute state (ADR 0001), faked mail, a frozen clock so the mute window
 * is deterministic, and a configured recipient (the feature's Background step).
 */
function usesFakeVulnerabilityCheck(string $recipient = 'security@example.test'): void
{
    beforeEach(function () use ($recipient) {
        Storage::fake('security');
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2026, 6, 9, 12, 0, 0));
        config(['security.alert_recipient' => $recipient]);
    });
}

/**
 * Fake `composer audit --format=json` so the check sees exactly these vulnerabilities.
 * Mirrors Composer's JSON shape: advisories keyed by package, the public advisory id
 * exposed both as `advisoryId` and as a GitHub source `remoteId`.
 *
 * @param  array<int, array{package: string, advisory: string, title?: string, severity?: string}>  $vulnerabilities
 */
function fakeComposerAudit(array $vulnerabilities): void
{
    $advisories = [];

    foreach ($vulnerabilities as $vulnerability) {
        $advisories[$vulnerability['package']][] = [
            'advisoryId' => $vulnerability['advisory'],
            'packageName' => $vulnerability['package'],
            'title' => $vulnerability['title'] ?? 'Vulnerability in '.$vulnerability['package'],
            'severity' => $vulnerability['severity'] ?? 'high',
            'sources' => [['name' => 'GitHub', 'remoteId' => $vulnerability['advisory']]],
        ];
    }

    Process::fake([
        '*' => Process::result(output: (string) json_encode(['advisories' => $advisories === [] ? [] : $advisories])),
    ]);
}

/**
 * Run the scheduled vulnerability check once at the current (test) clock.
 */
function runVulnerabilityCheck(): void
{
    Artisan::call('security:check-vulnerabilities');
}

/**
 * Establish that `$advisory` in `$package` was already reported `$hoursAgo` hours ago,
 * by running a real check at that moment with the vulnerability present. This writes
 * the real mute state to the faked `security` disk; the seeding mail is then discarded
 * so the run under test counts on its own.
 */
function reportVulnerability(string $package, string $advisory, int $hoursAgo): void
{
    $now = Carbon::now();

    Carbon::setTestNow($now->copy()->subHours($hoursAgo));
    fakeComposerAudit([['package' => $package, 'advisory' => $advisory]]);
    runVulnerabilityCheck();

    Carbon::setTestNow($now);
    Mail::fake();
}

/**
 * Run a check at `$hoursAgo` hours ago against an empty audit, so a previously
 * reported vulnerability is seen as resolved and its mute state is pruned.
 */
function reportVulnerabilityResolved(int $hoursAgo): void
{
    $now = Carbon::now();

    Carbon::setTestNow($now->copy()->subHours($hoursAgo));
    fakeComposerAudit([]);
    runVulnerabilityCheck();

    Carbon::setTestNow($now);
    Mail::fake();
}

/**
 * Set the site's stored theme preference directly via localStorage and reload
 * the page, so a browser scenario can start from a known mode without driving
 * the switcher UI to get there (the "Given I have switched the site theme to
 * X" precondition, shared with header_github_link.feature and
 * post_code_highlighting.feature, but here needing the real tri-state
 * light/dark/auto value rather than just prefers-color-scheme emulation).
 */
function switchSiteThemeTo(PendingAwaitablePage|AwaitableWebpage $page, string $mode): PendingAwaitablePage|AwaitableWebpage
{
    $page->script('localStorage.setItem('.json_encode('theme').', '.json_encode($mode).')');

    return $page->refresh();
}

/**
 * The bare label text a theme switcher menu option renders for a given mode
 * (documentation/leesmij/navigation/theme_switcher.md), used to assert what
 * the open menu's options display. Task 015 replaced each option's Unicode
 * glyph with an inline SVG icon, so the text content is the label alone —
 * the icon is asserted separately via themeModeIconSelector().
 */
function themeModeLabel(string $mode): string
{
    return match ($mode) {
        'light' => 'Light',
        'dark' => 'Dark',
        'auto' => 'Auto',
        default => throw new InvalidArgumentException("Unknown theme mode [{$mode}]."),
    };
}

/**
 * A CSS selector for the inline SVG icon a theme switcher control (the
 * closed button or a menu option) renders for a given mode
 * (documentation/leesmij/navigation/theme_switcher.md, task 015's swap from
 * Unicode glyphs to SVG icons matching the header's GitHub icon). Each
 * control's icon carries a `data-mode` attribute identifying which mode it
 * represents, mirroring the existing `[role="option"][data-mode="..."]`
 * convention used for menu options themselves.
 */
function themeModeIconSelector(string $mode): string
{
    return match ($mode) {
        'light', 'dark', 'auto' => "svg[data-mode=\"{$mode}\"]",
        default => throw new InvalidArgumentException("Unknown theme mode [{$mode}]."),
    };
}
