<?php

declare(strict_types=1);

namespace App\Security;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

final readonly class MuteWindow
{
    /**
     * Decide which current vulnerabilities to report now and the mute state to
     * persist. A vulnerability is reported when it is newly seen or when at least
     * 48 hours have passed since it was last reported; vulnerabilities no longer
     * present are pruned from the state.
     *
     * Two states come back, because whether a vulnerability counts as reported
     * depends on whether the alert actually went out. Both prune resolved
     * entries; they differ only in how they treat the reportable ones.
     *
     * @param  array<int, Vulnerability>  $vulnerabilities
     * @param  array<string, string>  $mutedAt
     */
    public function apply(array $vulnerabilities, array $mutedAt, CarbonInterface $now): VulnerabilityCheckResult
    {
        $reportable = [];
        $nextState = [];
        $unreportedState = [];

        foreach ($vulnerabilities as $vulnerability) {
            $key = $vulnerability->advisory.'|'.$vulnerability->package;

            if (! array_key_exists($key, $mutedAt) || $this->windowHasPassed($mutedAt[$key], $now)) {
                $reportable[] = $vulnerability;
                $nextState[$key] = $now->toIso8601String();

                // Keep the stamp that made this due, so a failed alert leaves it
                // due again on the next run instead of muting it for 48 hours.
                if (array_key_exists($key, $mutedAt)) {
                    $unreportedState[$key] = $mutedAt[$key];
                }

                continue;
            }

            $nextState[$key] = $mutedAt[$key];
            $unreportedState[$key] = $mutedAt[$key];
        }

        return new VulnerabilityCheckResult($reportable, $nextState, $unreportedState);
    }

    private function windowHasPassed(string $lastReportedAt, CarbonInterface $now): bool
    {
        // The mute window is 48 hours: a still-present vulnerability is reported
        // again once at least 48 hours have passed since it was last reported.
        return $now->greaterThanOrEqualTo(Carbon::parse($lastReportedAt)->addHours(48));
    }
}
