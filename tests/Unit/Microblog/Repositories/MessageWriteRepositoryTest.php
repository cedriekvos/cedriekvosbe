<?php

use App\Microblog\Repositories\MessageWriteRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

covers(MessageWriteRepository::class);

beforeEach(function () {
    Storage::fake('microblog');
    $this->writer = app(MessageWriteRepository::class);
});

it('creates a message on disk and returns its generated id', function () {
    Carbon::setTestNow(Carbon::create(2026, 5, 1, 14, 32, 0));

    $id = $this->writer->create('Just shipped a new feature!');

    expect($id)->not->toBe('');
    expect(Storage::disk('microblog')->exists($id.'.md'))->toBeTrue();
    expect(Storage::disk('microblog')->get($id.'.md'))
        ->toContain("posted_at: '2026-05-01 14:32:00'")
        ->toContain('Just shipped a new feature!');

    Carbon::setTestNow();
});

it('throws when updating a message that does not exist', function () {
    expect(fn () => $this->writer->update('ghost', 'body'))
        ->toThrow(RuntimeException::class, 'does not exist');
});

it('replaces the body while preserving the original posted_at on update', function () {
    Carbon::setTestNow(Carbon::create(2026, 1, 1, 0, 0, 0));
    $id = $this->writer->create('Origional typo');
    Carbon::setTestNow(Carbon::create(2026, 6, 1, 0, 0, 0));

    $this->writer->update($id, 'Original, fixed');

    $contents = Storage::disk('microblog')->get($id.'.md');
    expect($contents)
        ->toContain("posted_at: '2026-01-01 00:00:00'")
        ->toContain('Original, fixed')
        ->not->toContain('Origional typo');

    Carbon::setTestNow();
});

it('returns false when deleting a message that does not exist', function () {
    expect($this->writer->delete('nothing'))->toBeFalse();
});

it('removes the underlying file when deleting an existing message', function () {
    $id = $this->writer->create('Bye');

    expect($this->writer->delete($id))->toBeTrue();
    expect(Storage::disk('microblog')->exists($id.'.md'))->toBeFalse();
});
