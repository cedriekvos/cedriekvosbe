<?php

use App\Security\Audit\ComposerAudit;
use Illuminate\Support\Facades\Process;

covers(ComposerAudit::class);

it('runs composer audit as JSON and returns its raw output', function () {
    Process::fake([
        '*' => Process::result(output: '{"advisories":[]}'),
    ]);

    $output = (new ComposerAudit)->run();

    expect($output)->toContain('{"advisories":[]}');
    Process::assertRan('composer audit --format=json');
});

it('still returns the report when composer exits non-zero because it found advisories', function () {
    Process::fake([
        '*' => Process::result(output: '{"advisories":{"vendor/foo":[]}}', exitCode: 1),
    ]);

    expect((new ComposerAudit)->run())->toContain('vendor/foo');
});

it('fails loudly when the audit produced no report at all', function () {
    Process::fake([
        '*' => Process::result(output: '', errorOutput: 'composer: command not found', exitCode: 127),
    ]);

    expect(fn () => (new ComposerAudit)->run())
        ->toThrow(RuntimeException::class, 'composer audit produced no JSON report. composer: command not found');
});

it('fails loudly when the audit output is not json', function () {
    Process::fake([
        '*' => Process::result(output: 'Could not read composer.lock', exitCode: 1),
    ]);

    // Asserted in full: with nothing on stderr the message must not trail the separator.
    expect(fn () => (new ComposerAudit)->run())
        ->toThrow(new RuntimeException('composer audit produced no JSON report.'));
});
