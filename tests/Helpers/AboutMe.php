<?php

use App\About\About;
use App\About\Repositories\AboutRepository;

/**
 * Configure the homepage about-me section's singleton content, through the same
 * `AboutRepository` the admin form saves with, so a scenario seeds it exactly as
 * the editor would. Persisted as about.yaml on the (faked) `meta` disk.
 */
function configureAboutMe(string $heading = '', string $bio = ''): void
{
    app(AboutRepository::class)->save($heading, $bio);
}

/**
 * Read the about-me singleton back from storage as an About DTO — the read
 * side of configureAboutMe(), used to assert what the admin editor persisted.
 */
function storedAbout(): About
{
    return app(AboutRepository::class)->get();
}
