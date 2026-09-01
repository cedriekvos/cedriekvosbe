<?php

use App\About\About;
use App\About\AboutFactory;
use App\About\Markdown\AboutFileParser;
use App\About\Markdown\AboutFileSerializer;
use App\About\Repositories\AboutGetRepository;
use App\About\Repositories\AboutRepository;
use App\About\Repositories\AboutWriteRepository;
use App\About\Storage\AboutFileStorage;
use App\Blog\Markdown\PostMarkdownToHtmlConverter;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

covers(AboutRepository::class);

beforeEach(function () {
    Storage::fake('meta');

    $storage = new AboutFileStorage;
    $this->repository = new AboutRepository(
        new AboutGetRepository($storage, new AboutFileParser, new AboutFactory(new PostMarkdownToHtmlConverter(new CommonMarkCoreExtension, new ExternalLinkExtension))),
        new AboutWriteRepository($storage, new AboutFileSerializer),
    );
});

it('saves the about section and reads it back through the facade', function () {
    $this->repository->save('About me', 'I write about **software**.');

    $about = $this->repository->get();

    expect($about)->toBeInstanceOf(About::class)
        ->and($about->heading)->toBe('About me')
        ->and($about->bio_as_markdown)->toBe('I write about **software**.')
        ->and($about->bio_as_html)->toContain('<strong>software</strong>')
        ->and($about->is_visible)->toBeTrue();
});
