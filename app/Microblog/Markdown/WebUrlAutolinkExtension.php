<?php

declare(strict_types=1);

namespace App\Microblog\Markdown;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\Autolink\UrlAutolinkParser;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Renderer\Inline\LinkRenderer;
use League\CommonMark\Extension\ExtensionInterface;

/**
 * Turns a bare web URL inside otherwise plain text into a link.
 *
 * `AutolinkExtension` is not used as-is: it bundles the email autolinker and
 * defaults to the `ftp` protocol and to `http://` for scheme-less hosts. Only
 * the URL parser is registered here, configured for the web schemes alone.
 */
final readonly class WebUrlAutolinkExtension implements ExtensionInterface
{
    /**
     * A scheme-less `www.` URL gets an https destination rather than the
     * parser's own `http` default — there is no reason to send a visitor off
     * over plain HTTP.
     */
    private const string DEFAULT_PROTOCOL = 'https';

    /**
     * The allowed protocols are `http` and `https` only. `ftp://` is never
     * linked to from a message and only widens the attack surface, and
     * `javascript:` and `mailto:` are left out for the same reason.
     */
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addInlineParser(new UrlAutolinkParser(['http', 'https'], self::DEFAULT_PROTOCOL));
        $environment->addRenderer(Link::class, new LinkRenderer);
    }
}
