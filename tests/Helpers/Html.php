<?php

use Dom\Element;
use Dom\HTMLDocument;
use Illuminate\Support\Str;

/**
 * Parse HTML — a whole response or any fragment of one — with PHP's HTML5
 * parser, the way a browser would, so the helpers below query it with CSS
 * selectors instead of matching markup with regexes.
 */
function htmlDocument(string $html): HTMLDocument
{
    return HTMLDocument::createFromString($html, LIBXML_NOERROR, 'UTF-8');
}

/**
 * The visible text of a parsed element: its text content with runs of
 * whitespace collapsed.
 */
function htmlElementText(Element $element): string
{
    return Str::of($element->textContent)->replaceMatches('/\s+/', ' ')->trim()->value();
}

/**
 * An element's attributes as ` name="value"` pairs in source order, values
 * escaped the way Blade escapes them, so the result reads like the attribute
 * string of the element's opening tag.
 */
function htmlAttributeString(Element $element): string
{
    $attributes = '';

    foreach ($element->attributes as $attribute) {
        $attributes .= ' '.$attribute->name.'="'.e($attribute->value).'"';
    }

    return $attributes;
}

/**
 * The visible text of an HTML fragment: tags removed, entities decoded and runs
 * of whitespace collapsed, so a scenario can assert what a reader actually sees
 * rather than how it happens to be marked up.
 */
function htmlText(string $html): string
{
    return htmlElementText(htmlDocument($html)->documentElement);
}

/**
 * The visible text of an HTML fragment with every `<a>` element removed, so a
 * scenario can assert that a phrase sits outside of any link.
 */
function htmlTextOutsideLinks(string $html): string
{
    $document = htmlDocument($html);

    foreach ($document->querySelectorAll('a') as $link) {
        $link->remove();
    }

    return htmlElementText($document->documentElement);
}

/**
 * Read an HTML attribute out of an element's attribute string, parsed the way a
 * browser would (any quoting, entities decoded), returning an empty string when
 * the attribute is absent.
 */
function htmlAttribute(string $attributes, string $name): string
{
    return htmlDocument('<span '.$attributes.'>')->querySelector('span')?->getAttribute($name) ?? '';
}

/**
 * Parse every `<a>` element in a fragment of HTML, in document order, into the
 * href, visible link text and the attributes the link scenarios assert on.
 *
 * @return array<int, array{href: string, text: string, target: string, rel: string}>
 */
function htmlLinks(string $html): array
{
    $links = [];

    foreach (htmlDocument($html)->querySelectorAll('a') as $link) {
        $links[] = [
            'href' => $link->getAttribute('href') ?? '',
            'text' => htmlElementText($link),
            'target' => $link->getAttribute('target') ?? '',
            'rel' => $link->getAttribute('rel') ?? '',
        ];
    }

    return $links;
}

/**
 * Extract the HTML of a `<section data-section="{$name}">...</section>` block
 * (the existing `data-section="about-me"` convention in resources/views/home.blade.php),
 * so scenarios can assert content is scoped to that section rather than bleeding
 * into (or being confused with) another part of the page.
 *
 * Unlike the other helpers here this slices the string instead of parsing it:
 * the result must be a verbatim substring of $html, because scenarios cut it out
 * of the page with str_replace(), and re-serialized markup would not match.
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
 * The markup of one post's card on the homepage — the `<a href="/blog/{slug}">`
 * element that wraps it — so badge assertions can target a single post without
 * matching markup that happens to appear elsewhere on the page. Empty when the
 * page has no card for the slug.
 */
function postCardHtml(string $body, string $slug): string
{
    $document = htmlDocument($body);
    $card = $document->querySelector('a[href="/blog/'.$slug.'"]');

    return $card === null ? '' : $document->saveHtml($card);
}

/**
 * The attribute string of the first `<a>` pointing exactly at $href, or an empty
 * string when the page carries no such link.
 */
function htmlLinkAttributes(string $html, string $href): string
{
    $link = htmlDocument($html)->querySelector('a[href="'.$href.'"]');

    return $link === null ? '' : htmlAttributeString($link);
}

/**
 * The attribute string of the public header's GitHub profile link (the `<a>`
 * pointing at https://github.com/cedriekvos), so navigation scenarios can
 * assert its target/rel/aria-label/class without matching other page links.
 * Empty when the header carries no such link.
 */
function headerGithubLinkAttributes(string $html): string
{
    return htmlLinkAttributes($html, 'https://github.com/cedriekvos');
}

/**
 * The attribute string of the public header's LinkedIn profile link (the `<a>`
 * pointing at https://www.linkedin.com/in/cedriekvos), so navigation scenarios
 * can assert its target/rel/aria-label without matching other page links.
 * Empty when the header carries no such link.
 */
function headerLinkedinLinkAttributes(string $html): string
{
    return htmlLinkAttributes($html, 'https://www.linkedin.com/in/cedriekvos');
}

/**
 * Return the opening `<a>` tags in a rendered admin screen that point at a
 * preview URL (any href ending in `/preview`), so a test can assert the
 * preview button's target and new-tab behaviour without pinning its label.
 *
 * @return list<string>
 */
function previewButtonTags(string $html): array
{
    return array_map(
        fn (Element $link): string => '<a'.htmlAttributeString($link).'>',
        iterator_to_array(htmlDocument($html)->querySelectorAll('a[href$="/preview"]')),
    );
}
