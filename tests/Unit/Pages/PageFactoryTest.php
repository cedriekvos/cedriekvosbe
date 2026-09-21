<?php

use App\Pages\Page;
use App\Pages\PageFactory;

covers(PageFactory::class);

beforeEach(function () {
    $this->factory = new PageFactory;
});

it('builds a page from parsed data', function () {
    $page = $this->factory->make([
        'slug' => 'hello-world',
        'title' => 'Hello World',
        'body' => 'The body',
        'content' => '<p>The body</p>',
    ], isDraft: false);

    expect($page)->toBeInstanceOf(Page::class)
        ->and($page->slug)->toBe('hello-world')
        ->and($page->title)->toBe('Hello World')
        ->and($page->body)->toBe('The body')
        ->and($page->content)->toBe('<p>The body</p>')
        ->and($page->is_draft)->toBeFalse();
});

it('marks the page as a draft when told to', function () {
    $page = $this->factory->make(['slug' => 'wip'], isDraft: true);

    expect($page->is_draft)->toBeTrue();
});

it('defaults missing or non-string fields to empty strings', function () {
    $page = $this->factory->make([
        'slug' => 'partial',
        'title' => 123,
    ], isDraft: false);

    expect($page->slug)->toBe('partial')
        ->and($page->title)->toBe('')
        ->and($page->body)->toBe('')
        ->and($page->content)->toBe('');
});
