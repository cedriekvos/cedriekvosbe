<?php

// blog_redirect.feature — Scenario: Visiting /blog redirects to the homepage
it('redirects /blog to the home page with a 302 status code', function () {
    $this->get('/blog')
        ->assertRedirect('/')
        ->assertStatus(302);
});
