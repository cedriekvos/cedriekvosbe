<?php

use Illuminate\Support\Str;

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
 * The attribute string of the public header's LinkedIn profile link (the `<a>`
 * pointing at https://www.linkedin.com/in/cedriekvos), so navigation scenarios
 * can assert its target/rel/aria-label without matching other page links.
 * Empty when the header carries no such link.
 */
function headerLinkedinLinkAttributes(string $html): string
{
    preg_match('/<a\b([^>]*\bhref="https:\/\/www\.linkedin\.com\/in\/cedriekvos"[^>]*)>/i', $html, $match);

    return $match[1] ?? '';
}

/**
 * Return the opening `<a>` tags in a rendered admin screen that point at a
 * preview URL (any href containing `/preview`), so a test can assert the
 * preview button's target and new-tab behaviour without pinning its label.
 *
 * @return list<string>
 */
function previewButtonTags(string $html): array
{
    preg_match_all('/<a\b[^>]*href="[^"]*\/preview"[^>]*>/i', $html, $matches);

    return $matches[0];
}
