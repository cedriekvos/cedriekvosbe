<?php

use App\About\About;
use App\About\AboutFactory;
use App\Blog\Markdown\PostMarkdownToHtmlConverter;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

covers(AboutFactory::class);

beforeEach(function () {
    $this->factory = new AboutFactory(
        new PostMarkdownToHtmlConverter(new CommonMarkCoreExtension, new ExternalLinkExtension),
    );
});

it('builds an about section and renders the bio to HTML', function () {
    $about = $this->factory->make([
        'heading' => 'About me',
        'bio' => 'I write about **software**.',
    ]);

    expect($about)->toBeInstanceOf(About::class)
        ->and($about->heading)->toBe('About me')
        ->and($about->bio_as_markdown)->toBe('I write about **software**.')
        ->and($about->bio_as_html)->toContain('<strong>software</strong>')
        ->and($about->is_visible)->toBeTrue();
});

it('defaults missing or non-string fields to empty strings', function () {
    $about = $this->factory->make(['heading' => 123]);

    expect($about->heading)->toBe('')
        ->and($about->bio_as_markdown)->toBe('')
        ->and($about->bio_as_html)->toBe('')
        ->and($about->is_visible)->toBeFalse();
});

it('is visible whenever the heading or the bio has content', function (string $heading, string $bio, bool $visible) {
    expect($this->factory->make(['heading' => $heading, 'bio' => $bio])->is_visible)->toBe($visible);
})->with([
    'heading and bio' => ['About me', 'I build things.', true],
    'heading only' => ['About me', '', true],
    'bio only' => ['', 'I build things.', true],
    'both empty' => ['', '', false],
]);
