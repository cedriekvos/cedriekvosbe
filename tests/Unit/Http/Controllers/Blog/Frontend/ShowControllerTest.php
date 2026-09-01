<?php

use App\Http\Controllers\Blog\Frontend\ShowController;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

covers(ShowController::class);

beforeEach(function () {
    Storage::fake('posts');
});

it('returns the blog.show view with the requested post', function () {
    writePostFile('welcome', 'Welcome', '2026-05-01');

    $response = (new ShowController)(postGetRepository(), 'welcome');

    expect($response)
        ->toBeInstanceOf(View::class)
        ->and($response->name())->toBe('blog.show')
        ->and($response->getData()['post']->slug)->toBe('welcome');
});

it('aborts with a 404 when the post does not exist', function () {
    expect(fn () => (new ShowController)(postGetRepository(), 'missing'))
        ->toThrow(NotFoundHttpException::class);
});
