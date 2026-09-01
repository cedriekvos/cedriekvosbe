<?php

use App\About\Markdown\AboutFileSerializer;
use App\About\Repositories\AboutWriteRepository;
use App\About\Storage\AboutFileStorage;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

covers(AboutWriteRepository::class);

beforeEach(function () {
    Storage::fake('meta');
    $this->repository = new AboutWriteRepository(new AboutFileStorage, new AboutFileSerializer);
});

it('serialises and writes the heading and bio to storage', function () {
    $this->repository->save('About me', 'I write about **software**.');

    $stored = Storage::disk('meta')->get('about.yaml') ?? '';

    expect(Yaml::parse($stored))->toBe([
        'heading' => 'About me',
        'bio' => 'I write about **software**.',
    ]);
});
