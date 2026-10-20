<?php

use Dom\Element;
use Dom\HTMLDocument;
use Illuminate\Support\Str;

/**
 * Parse HTML — a whole response or any fragment of one — with PHP's HTML5
 * parser, the way a browser would, so scenarios query it with CSS selectors
 * instead of matching markup with regexes.
 */
function htmlDocument(string $html): HTMLDocument
{
    return HTMLDocument::createFromString($html, LIBXML_NOERROR, 'UTF-8');
}

/**
 * Every element in $html matching a CSS selector, in document order.
 *
 * @return list<Element>
 */
function htmlElements(string $html, string $selector): array
{
    return iterator_to_array(htmlDocument($html)->querySelectorAll($selector));
}

/**
 * The first element in $html matching a CSS selector. Fails the test when
 * nothing matches, so a scenario can read the element's attributes straight away.
 */
function htmlElement(string $html, string $selector): Element
{
    $element = htmlDocument($html)->querySelector($selector);
    expect($element)->not->toBeNull("No element matches [{$selector}].");

    return $element;
}

/**
 * The markup of a parsed element, re-serialized from the parsed document.
 */
function htmlElementHtml(Element $element): string
{
    return $element->ownerDocument->saveHtml($element);
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
 * Parse every `<a>` element in a fragment of HTML, in document order, into the
 * href, visible link text and the attributes the link scenarios assert on.
 *
 * @return array<int, array{href: string, text: string, target: string, rel: string}>
 */
function htmlLinks(string $html): array
{
    return array_map(
        fn (Element $link): array => [
            'href' => $link->getAttribute('href') ?? '',
            'text' => htmlElementText($link),
            'target' => $link->getAttribute('target') ?? '',
            'rel' => $link->getAttribute('rel') ?? '',
        ],
        htmlElements($html, 'a'),
    );
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
