<?php

use App\Microblog\Message;
use App\Microblog\MessageFactory;

covers(MessageFactory::class);

beforeEach(function () {
    $this->factory = messageFactory();
});

it('builds a message from parsed data', function () {
    $message = $this->factory->make([
        'id' => '01ARZ3NDEKTSV4RRFFQ69G5FAV',
        'body' => 'Just shipped a new feature!',
        'posted_at' => '2026-05-01 14:32:00',
    ]);

    expect($message)->toBeInstanceOf(Message::class)
        ->and($message->id)->toBe('01ARZ3NDEKTSV4RRFFQ69G5FAV')
        ->and($message->body)->toBe('Just shipped a new feature!')
        ->and($message->posted_at)->toBe('2026-05-01 14:32:00');
});

it('renders the body to html with its URLs linked', function () {
    $message = $this->factory->make(['body' => 'Zie https://php.net']);

    expect($message->body)->toBe('Zie https://php.net')
        ->and($message->body_as_html)->toContain('<a')
        ->and($message->body_as_html)->toContain('href="https://php.net"');
});

it('defaults missing or non-string fields to empty strings', function () {
    $message = $this->factory->make([
        'id' => 'partial',
        'body' => 123,
    ]);

    expect($message->id)->toBe('partial')
        ->and($message->body)->toBe('')
        ->and($message->body_as_html)->toBe('')
        ->and($message->posted_at)->toBe('');
});
