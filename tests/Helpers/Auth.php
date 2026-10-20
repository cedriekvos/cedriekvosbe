<?php

use App\Models\User;

/**
 * Sign in as a freshly created editor account before each test
 * (the admin features' "Given I am signed in as an editor" Background step).
 */
function signsInAsEditor(): void
{
    beforeEach(function () {
        $this->actingAs(User::factory()->create());
    });
}
