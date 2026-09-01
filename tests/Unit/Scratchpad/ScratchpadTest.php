<?php

use App\Scratchpad\Scratchpad;

covers(Scratchpad::class);

it('exposes the content passed to its constructor', function () {
    $scratchpad = new Scratchpad('Remember to check backlinks.');

    expect($scratchpad->content)->toBe('Remember to check backlinks.');
});
