<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Browser tests assert on real, browser-computed styles (e.g. syntax
 * highlighting colours), so unlike TestCase they keep the actual compiled
 * Vite assets. It extends the framework's TestCase, not Tests\TestCase, on
 * purpose: setup added to TestCase does not reach the browser suite and must
 * be repeated here when browser tests need it too.
 */
abstract class BrowserTestCase extends BaseTestCase {}
