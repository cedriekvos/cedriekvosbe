<?php

use App\Livewire\Admin\PostIndex;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

covers(PostIndex::class);

beforeEach(function () {
    Storage::fake('posts');
});

it('hands the view every post, drafts included', function () {
    writePostFile('published', 'Published', '2026-01-01');
    writePostFile('draft-unfinished', 'Unfinished', '2026-02-01');

    $posts = Livewire::test(PostIndex::class)->viewData('posts');

    expect(array_column($posts, 'slug'))->toEqualCanonicalizing(['published', 'draft-unfinished']);
});

it('deletes the post and shows a status naming its slug', function () {
    writePostFile('goner', 'Goner', '2026-01-01');

    $component = Livewire::test(PostIndex::class)->call('delete', 'goner');

    $component->assertSee('Post [goner] deleted.');
    expect(Storage::disk('posts')->exists('goner.md'))->toBeFalse();
});
