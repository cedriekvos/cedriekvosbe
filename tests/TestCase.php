<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Swap Vite for the framework's no-op stand-in, so views render without
     * compiled assets. Browser tests need the real assets and extend
     * BrowserTestCase instead.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}
