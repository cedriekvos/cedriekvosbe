<?php

use App\Microblog\Message;

covers(Message::class);

it('exposes every value passed to its constructor', function () {
    $message = new Message(
        id: '01ARZ3NDEKTSV4RRFFQ69G5FAV',
        body: 'Just shipped a new feature!',
        body_as_html: '<p>Just shipped a new feature!</p>',
        posted_at: '2026-05-01 14:32:00',
    );

    expect($message->id)->toBe('01ARZ3NDEKTSV4RRFFQ69G5FAV')
        ->and($message->body)->toBe('Just shipped a new feature!')
        ->and($message->body_as_html)->toBe('<p>Just shipped a new feature!</p>')
        ->and($message->posted_at)->toBe('2026-05-01 14:32:00');
});

it('can be constructed from a parsed message array via named-argument spreading', function () {
    $attributes = [
        'id' => 'from-array',
        'body' => 'body',
        'body_as_html' => '<p>body</p>',
        'posted_at' => '2026-02-02 00:00:00',
    ];

    $message = new Message(...$attributes);

    expect($message->id)->toBe('from-array')
        ->and($message->posted_at)->toBe('2026-02-02 00:00:00');
});
