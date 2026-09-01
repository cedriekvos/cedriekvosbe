<?php

use App\Security\MuteWindow;
use App\Security\Vulnerability;
use App\Security\VulnerabilityCheckResult;
use Illuminate\Support\Carbon;

covers(MuteWindow::class);

beforeEach(function () {
    $this->window = new MuteWindow;
    $this->now = Carbon::create(2026, 6, 9, 12, 0, 0);
});

it('reports a newly seen vulnerability and records it as reported now', function () {
    $vulnerability = new Vulnerability('vendor/foo', 'GHSA-aaaa', 'title', 'high');

    $result = $this->window->apply([$vulnerability], [], $this->now);

    expect($result)->toBeInstanceOf(VulnerabilityCheckResult::class)
        ->and($result->reportable)->toBe([$vulnerability])
        ->and($result->nextState)->toBe(['GHSA-aaaa|vendor/foo' => $this->now->toIso8601String()]);
});

it('reports every newly seen vulnerability in a single pass', function () {
    $foo = new Vulnerability('vendor/foo', 'GHSA-aaaa', 'title', 'high');
    $bar = new Vulnerability('vendor/bar', 'GHSA-bbbb', 'title', 'high');

    $result = $this->window->apply([$foo, $bar], [], $this->now);

    expect($result->reportable)->toBe([$foo, $bar])
        ->and($result->nextState)->toBe([
            'GHSA-aaaa|vendor/foo' => $this->now->toIso8601String(),
            'GHSA-bbbb|vendor/bar' => $this->now->toIso8601String(),
        ]);
});

it('mutes a vulnerability reported less than 48 hours ago and keeps its timestamp', function () {
    $lastReported = $this->now->copy()->subHours(47)->toIso8601String();
    $vulnerability = new Vulnerability('vendor/foo', 'GHSA-aaaa', 'title', 'high');

    $result = $this->window->apply([$vulnerability], ['GHSA-aaaa|vendor/foo' => $lastReported], $this->now);

    expect($result->reportable)->toBe([])
        ->and($result->nextState)->toBe(['GHSA-aaaa|vendor/foo' => $lastReported]);
});

it('reports again exactly 48 hours after the last report', function () {
    $lastReported = $this->now->copy()->subHours(48)->toIso8601String();
    $vulnerability = new Vulnerability('vendor/foo', 'GHSA-aaaa', 'title', 'high');

    $result = $this->window->apply([$vulnerability], ['GHSA-aaaa|vendor/foo' => $lastReported], $this->now);

    expect($result->reportable)->toBe([$vulnerability])
        ->and($result->nextState)->toBe(['GHSA-aaaa|vendor/foo' => $this->now->toIso8601String()]);
});

it('reports again well after the mute window has passed', function () {
    $lastReported = $this->now->copy()->subHours(72)->toIso8601String();
    $vulnerability = new Vulnerability('vendor/foo', 'GHSA-aaaa', 'title', 'high');

    $result = $this->window->apply([$vulnerability], ['GHSA-aaaa|vendor/foo' => $lastReported], $this->now);

    expect($result->reportable)->toBe([$vulnerability]);
});

it('prunes vulnerabilities that are no longer present', function () {
    $result = $this->window->apply([], ['GHSA-aaaa|vendor/foo' => $this->now->toIso8601String()], $this->now);

    expect($result->reportable)->toBe([])
        ->and($result->nextState)->toBe([]);
});

it('leaves a newly seen vulnerability out of the unreported state so it stays due', function () {
    $vulnerability = new Vulnerability('vendor/foo', 'GHSA-aaaa', 'title', 'high');

    $result = $this->window->apply([$vulnerability], [], $this->now);

    expect($result->unreportedState)->toBe([]);
});

it('keeps the previous timestamp in the unreported state when the window had passed', function () {
    $lastReported = $this->now->copy()->subHours(48)->toIso8601String();
    $vulnerability = new Vulnerability('vendor/foo', 'GHSA-aaaa', 'title', 'high');

    $result = $this->window->apply([$vulnerability], ['GHSA-aaaa|vendor/foo' => $lastReported], $this->now);

    expect($result->unreportedState)->toBe(['GHSA-aaaa|vendor/foo' => $lastReported]);
});

it('keeps muted entries in the unreported state', function () {
    $lastReported = $this->now->copy()->subHours(1)->toIso8601String();
    $vulnerability = new Vulnerability('vendor/foo', 'GHSA-aaaa', 'title', 'high');

    $result = $this->window->apply([$vulnerability], ['GHSA-aaaa|vendor/foo' => $lastReported], $this->now);

    expect($result->unreportedState)->toBe(['GHSA-aaaa|vendor/foo' => $lastReported]);
});

it('prunes resolved vulnerabilities from the unreported state too', function () {
    $result = $this->window->apply([], ['GHSA-aaaa|vendor/foo' => $this->now->toIso8601String()], $this->now);

    expect($result->unreportedState)->toBe([]);
});

it('keys mute state by advisory and package together', function () {
    $foo = new Vulnerability('vendor/foo', 'GHSA-aaaa', 'title', 'high');
    $bar = new Vulnerability('vendor/bar', 'GHSA-aaaa', 'title', 'high');
    $recent = $this->now->copy()->subHour()->toIso8601String();

    // foo is muted; bar shares the advisory id but is a different package, so it is still reported
    $result = $this->window->apply([$foo, $bar], ['GHSA-aaaa|vendor/foo' => $recent], $this->now);

    expect($result->reportable)->toBe([$bar])
        ->and($result->nextState)->toBe([
            'GHSA-aaaa|vendor/foo' => $recent,
            'GHSA-aaaa|vendor/bar' => $this->now->toIso8601String(),
        ]);
});
