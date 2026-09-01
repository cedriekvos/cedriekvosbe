<?php

use App\About\Markdown\AboutFileSerializer;
use Symfony\Component\Yaml\Yaml;

covers(AboutFileSerializer::class);

it('serialises the heading and bio to YAML', function () {
    $yaml = (new AboutFileSerializer)->serialize('About me', 'I write about **software**.');

    expect($yaml)->toBeString()
        ->and(Yaml::parse($yaml))->toBe([
            'heading' => 'About me',
            'bio' => 'I write about **software**.',
        ]);
});
