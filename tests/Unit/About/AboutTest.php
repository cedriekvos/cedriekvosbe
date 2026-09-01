<?php

use App\About\About;

covers(About::class);

it('exposes every value passed to its constructor', function () {
    $about = new About(
        heading: 'About me',
        bio_as_markdown: 'Hi, I write **software**.',
        bio_as_html: '<p>Hi, I write <strong>software</strong>.</p>',
        is_visible: true,
    );

    expect($about->heading)->toBe('About me')
        ->and($about->bio_as_markdown)->toBe('Hi, I write **software**.')
        ->and($about->bio_as_html)->toBe('<p>Hi, I write <strong>software</strong>.</p>')
        ->and($about->is_visible)->toBeTrue();
});
