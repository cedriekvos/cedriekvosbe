<?php

use App\About\About;
use App\About\Repositories\AboutRepository;
use App\Http\Controllers\Blog\Frontend\IndexController;
use App\Microblog\Repositories\MessagesRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

covers(IndexController::class);

beforeEach(function () {
    Storage::fake('posts');
    Storage::fake('meta');
    Storage::fake('microblog');
});

it('returns the home view with the published posts newest first', function () {
    writePostFile('older', 'Older', '2026-01-01');
    writePostFile('newest', 'Newest', '2026-05-01');
    writePostFile('draft-wip', 'WIP', '2026-09-01');

    $response = (new IndexController)(postGetRepository(), app(AboutRepository::class), app(MessagesRepository::class));

    expect($response)
        ->toBeInstanceOf(View::class)
        ->and($response->name())->toBe('home')
        ->and(array_column($response->getData()['posts'], 'slug'))->toBe(['newest', 'older']);
});

it('passes the about-me section to the home view', function () {
    app(AboutRepository::class)->save('About me', 'I write about **software**.');

    $response = (new IndexController)(postGetRepository(), app(AboutRepository::class), app(MessagesRepository::class));

    expect($response->getData()['about'])
        ->toBeInstanceOf(About::class)
        ->and($response->getData()['about']->heading)->toBe('About me');
});

it('passes the messages sorted newest first to the home view', function () {
    writeMessageFile('older', 'Older', '2026-01-01 00:00:00');
    writeMessageFile('newer', 'Newer', '2026-06-01 00:00:00');

    $response = (new IndexController)(postGetRepository(), app(AboutRepository::class), app(MessagesRepository::class));

    expect(array_column($response->getData()['messages'], 'id'))->toBe(['newer', 'older']);
});
