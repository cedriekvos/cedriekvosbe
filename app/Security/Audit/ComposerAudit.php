<?php

declare(strict_types=1);

namespace App\Security\Audit;

use Illuminate\Support\Facades\Process;
use RuntimeException;

final readonly class ComposerAudit
{
    private const string COMMAND = 'composer audit --format=json';

    /**
     * Run Composer's dependency audit and return its raw JSON report.
     *
     * The exit code cannot stand in for failure here: `composer audit` exits
     * non-zero precisely when it *finds* advisories. Output that is not JSON
     * means the command never produced a report — a missing binary, say — which
     * must fail loudly rather than read as a clean audit.
     *
     * @throws RuntimeException when the audit produced no JSON report.
     */
    public function run(): string
    {
        $result = Process::run(self::COMMAND);
        $output = $result->output();

        if (! is_array(json_decode($output, true))) {
            throw new RuntimeException(rtrim('composer audit produced no JSON report. '.$result->errorOutput()));
        }

        return $output;
    }
}
