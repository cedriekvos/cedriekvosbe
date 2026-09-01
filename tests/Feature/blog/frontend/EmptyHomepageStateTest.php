<?php

usesFakePostsRepository();

// empty_homepage_state.feature — Scenario: No posts have been published
it('renders an empty home page when no posts exist', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('no posts yet', escape: false);
});
