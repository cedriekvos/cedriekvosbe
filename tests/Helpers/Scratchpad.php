<?php

use App\Scratchpad\Repositories\ScratchpadRepository;
use App\Scratchpad\Scratchpad;

/**
 * Configure the admin scratchpad's singleton content, through the same
 * `ScratchpadRepository` the admin form saves with. Persisted as scratchpad.md on
 * the (faked) `meta` disk, alongside the about-me content.
 */
function configureScratchpad(string $content = ''): void
{
    app(ScratchpadRepository::class)->save($content);
}

/**
 * Read the scratchpad singleton back from storage as a Scratchpad DTO — the
 * read side of configureScratchpad(), used to assert what the admin editor persisted.
 */
function storedScratchpad(): Scratchpad
{
    return app(ScratchpadRepository::class)->get();
}
