<?php

use App\View\Components\GuestLayout;

covers(GuestLayout::class);

it('renders the layouts.guest view', function () {
    expect((new GuestLayout)->render()->name())->toBe('layouts.guest');
});
