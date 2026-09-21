<?php

use App\Http\Controllers\Pages\Frontend\ShowController;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

covers(ShowController::class);

beforeEach(function () {
    Storage::fake('pages');
});

it('returns the pages.show view with the requested page', function () {
    writePageFile('welcome', 'Welcome');

    $response = (new ShowController)(pageGetRepository(), 'welcome');

    expect($response)
        ->toBeInstanceOf(View::class)
        ->and($response->name())->toBe('pages.show')
        ->and($response->getData()['page']->slug)->toBe('welcome');
});

it('aborts with a 404 when the page does not exist', function () {
    expect(fn () => (new ShowController)(pageGetRepository(), 'missing'))
        ->toThrow(NotFoundHttpException::class);
});
