<?php

use App\Livewire\Admin\PageIndex;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

covers(PageIndex::class);

beforeEach(function () {
    Storage::fake('pages');
});

it('hands the view every page, drafts included', function () {
    writePageFile('published', 'Published');
    writePageFile('draft-unfinished', 'Unfinished');

    $pages = Livewire::test(PageIndex::class)->viewData('pages');

    expect(array_column($pages, 'slug'))->toEqualCanonicalizing(['published', 'draft-unfinished']);
});

it('deletes the page and shows a status naming its slug', function () {
    writePageFile('goner', 'Goner');

    $component = Livewire::test(PageIndex::class)->call('delete', 'goner');

    $component->assertSee('Page [goner] deleted.');
    expect(Storage::disk('pages')->exists('goner.md'))->toBeFalse();
});
