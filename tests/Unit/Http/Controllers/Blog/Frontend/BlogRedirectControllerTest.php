<?php

use App\Http\Controllers\Blog\Frontend\BlogRedirectController;
use Illuminate\Http\RedirectResponse;

covers(BlogRedirectController::class);

it('returns a redirect response to the home page', function () {
    $response = (new BlogRedirectController)();

    expect($response)
        ->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toBe(url('/'))
        ->and($response->getStatusCode())->toBe(302);
});
