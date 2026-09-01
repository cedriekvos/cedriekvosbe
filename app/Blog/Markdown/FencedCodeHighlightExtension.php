<?php

declare(strict_types=1);

namespace App\Blog\Markdown;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\ExtensionInterface;
use Tempest\Highlight\CommonMark\CodeBlockRenderer;
use Tempest\Highlight\Highlighter;

final readonly class FencedCodeHighlightExtension implements ExtensionInterface
{
    public function __construct(
        private Highlighter $highlighter,
    ) {}

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addRenderer(FencedCode::class, new CodeBlockRenderer($this->highlighter), 10); // @pest-mutate-ignore: IncrementInteger,DecrementInteger
    }
}
