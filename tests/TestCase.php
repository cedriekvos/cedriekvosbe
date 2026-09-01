<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Browser tests assert on real, browser-computed styles (e.g. syntax
        // highlighting colours), so they need the actual compiled Vite assets
        // rather than the no-op stand-in withoutVite() installs for every
        // other suite. Pest namespaces generated test case classes after the
        // directory they live in (e.g. P\Tests\Browser\...), which is a
        // reliable way to tell the two apart.
        if (! str_contains(static::class, '\\Browser\\')) {
            $this->withoutVite();
        }
    }
}
