<?php

use App\Blog\DraftSlug;

covers(DraftSlug::class);

beforeEach(function () {
    $this->draftSlug = new DraftSlug;
});

it('recognises a slug carrying the draft prefix', function () {
    expect($this->draftSlug->isDraft('draft-wip'))->toBeTrue();
});

it('does not treat a published slug as a draft', function () {
    expect($this->draftSlug->isDraft('published'))->toBeFalse();
});

it('does not treat the prefix appearing later in the slug as a draft', function () {
    expect($this->draftSlug->isDraft('notes-draft-one'))->toBeFalse();
});

it('prefixes a base slug when the post is a draft', function () {
    expect($this->draftSlug->apply('hello', true))->toBe('draft-hello');
});

it('leaves a base slug alone when the post is published', function () {
    expect($this->draftSlug->apply('hello', false))->toBe('hello');
});

it('strips the prefix from a draft slug', function () {
    expect($this->draftSlug->strip('draft-hello'))->toBe('hello');
});

it('leaves a published slug unchanged when stripping', function () {
    expect($this->draftSlug->strip('hello'))->toBe('hello');
});

it('strips only the leading prefix, once', function () {
    expect($this->draftSlug->strip('draft-draft-hello'))->toBe('draft-hello');
});

it('round-trips a base slug through apply and strip', function () {
    expect($this->draftSlug->strip($this->draftSlug->apply('hello', true)))->toBe('hello');
});
