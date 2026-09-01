<?php

declare(strict_types=1);

namespace App\Markdown;

use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\MarkdownConverter;

/**
 * Renders Markdown to HTML for every domain that stores Markdown. Callers
 * supply the extensions they need; the security posture and the excerpt rules
 * are the same everywhere and live here.
 */
final readonly class MarkdownToHtmlConverter
{
    private MarkdownConverter $markdownConverter;

    /**
     * Rendered output is echoed unescaped into Blade, so raw HTML in the source
     * is escaped rather than trusted and `javascript:`-style links are dropped.
     * Authoring is admin-only, but that keeps a compromised editor account from
     * turning a post into stored XSS against every visitor.
     *
     * @param  array<int, ExtensionInterface>  $extensions
     */
    public function __construct(array $extensions)
    {
        $url = config('app.url');
        $host = is_string($url) ? parse_url($url, PHP_URL_HOST) : null;

        $environment = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'external_link' => [
                'internal_hosts' => is_string($host) ? [$host] : [],
                'open_in_new_window' => true,
            ],
        ]);

        foreach ($extensions as $extension) {
            $environment->addExtension($extension);
        }

        $this->markdownConverter = new MarkdownConverter($environment);
    }

    public function convert(string $markdown): string
    {
        try {
            return $this->markdownConverter->convert($markdown)->getContent();
        } catch (CommonMarkException $e) {
            report($e);

            return '';
        }
    }

    public function excerpt(string $markdown, int $maxLength = 160): string
    {
        $text = strip_tags($this->convert($markdown));
        $text = Str::of($text)->replaceMatches('/\s+/', ' ')->trim()->value();

        if (mb_strlen($text) > $maxLength) {
            return mb_substr($text, 0, $maxLength - 1).'…';
        }

        return $text;
    }
}
