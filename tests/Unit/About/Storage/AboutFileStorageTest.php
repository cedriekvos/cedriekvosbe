<?php

use App\About\Storage\AboutFileStorage;
use Illuminate\Support\Facades\Storage;

covers(AboutFileStorage::class);

beforeEach(function () {
    Storage::fake('meta');
    $this->storage = new AboutFileStorage;
});

it('returns an empty string when nothing has been written', function () {
    expect($this->storage->read())->toBe('');
});

it('writes contents and reads them back', function () {
    $this->storage->write('heading: About me');

    expect($this->storage->read())->toBe('heading: About me');
});

it('stores the file on the dedicated meta disk, separate from posts', function () {
    $this->storage->write('heading: About me');

    expect(Storage::disk('meta')->exists('about.yaml'))->toBeTrue();
});
