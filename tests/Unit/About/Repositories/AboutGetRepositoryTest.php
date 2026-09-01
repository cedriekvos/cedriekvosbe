<?php

use App\About\About;
use App\About\AboutFactory;
use App\About\Markdown\AboutFileParser;
use App\About\Repositories\AboutGetRepository;
use App\About\Storage\AboutFileStorage;
use App\Blog\Markdown\PostMarkdownToHtmlConverter;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

covers(AboutGetRepository::class);

beforeEach(function () {
    Storage::fake('meta');
    $this->repository = new AboutGetRepository(
        new AboutFileStorage,
        new AboutFileParser,
        new AboutFactory(new PostMarkdownToHtmlConverter(new CommonMarkCoreExtension, new ExternalLinkExtension)),
    );
});

it('builds the about section from the stored file', function () {
    Storage::disk('meta')->put('about.yaml', "heading: 'About me'\nbio: 'I write about **software**.'");

    $about = $this->repository->get();

    expect($about)->toBeInstanceOf(About::class)
        ->and($about->heading)->toBe('About me')
        ->and($about->bio_as_html)->toContain('<strong>software</strong>')
        ->and($about->is_visible)->toBeTrue();
});

it('returns a hidden about section when nothing is stored', function () {
    $about = $this->repository->get();

    expect($about->heading)->toBe('')
        ->and($about->bio_as_markdown)->toBe('')
        ->and($about->is_visible)->toBeFalse();
});
