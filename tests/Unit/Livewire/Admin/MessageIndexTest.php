<?php

use App\Livewire\Admin\MessageIndex;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

covers(MessageIndex::class);

beforeEach(function () {
    Storage::fake('microblog');
});

it('hands the view every message', function () {
    writeMessageFile('first', 'First', '2026-01-01 09:00:00');
    writeMessageFile('second', 'Second', '2026-01-02 09:00:00');

    $messages = Livewire::test(MessageIndex::class)->viewData('messages');

    expect(array_column($messages, 'id'))->toEqualCanonicalizing(['first', 'second']);
});

it('deletes the message and shows a status', function () {
    writeMessageFile('goner', 'Goner', '2026-01-01 09:00:00');

    $component = Livewire::test(MessageIndex::class)->call('delete', 'goner');

    $component->assertSee('Message deleted.');
    expect(Storage::disk('microblog')->exists('goner.md'))->toBeFalse();
});
