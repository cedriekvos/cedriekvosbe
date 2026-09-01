<?php

use App\Microblog\Repositories\MessagesRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

covers(MessagesRepository::class);

beforeEach(function () {
    Storage::fake('microblog');
    $this->repository = app(MessagesRepository::class);
});

it('writes a message and reads it back through the facade', function () {
    $id = $this->repository->create('Hello world');

    $message = $this->repository->find($id);

    expect($message->id)->toBe($id)
        ->and($message->body)->toBe('Hello world');
});

it('returns every message sorted newest first', function () {
    Carbon::setTestNow(Carbon::create(2026, 1, 1, 0, 0, 0));
    $older = $this->repository->create('Older');
    Carbon::setTestNow(Carbon::create(2026, 6, 1, 0, 0, 0));
    $newer = $this->repository->create('Newer');
    Carbon::setTestNow();

    expect(array_column($this->repository->all(), 'id'))->toBe([$newer, $older]);
});

it('reports existence of messages by id', function () {
    $id = $this->repository->create('Hi');

    expect($this->repository->exists($id))->toBeTrue();
    expect($this->repository->exists('nope'))->toBeFalse();
});

it('updates a message while preserving its original posted_at', function () {
    Carbon::setTestNow(Carbon::create(2026, 1, 1, 0, 0, 0));
    $id = $this->repository->create('Origional typo');
    Carbon::setTestNow(Carbon::create(2026, 6, 1, 0, 0, 0));

    $this->repository->update($id, 'Original, fixed');

    $message = $this->repository->find($id);
    expect($message->body)->toBe('Original, fixed')
        ->and($message->posted_at)->toBe('2026-01-01 00:00:00');

    Carbon::setTestNow();
});

it('removes the underlying file when deleting an existing message', function () {
    $id = $this->repository->create('Bye');

    expect($this->repository->delete($id))->toBeTrue();
    expect(Storage::disk('microblog')->exists($id.'.md'))->toBeFalse();
});

it('stores messages under storage/app/private/content/microblog', function () {
    expect(config('filesystems.disks.microblog.root'))->toBe(storage_path('app/private/content/microblog'));
});
