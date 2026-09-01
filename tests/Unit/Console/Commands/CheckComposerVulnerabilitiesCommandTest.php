<?php

use App\Console\Commands\CheckComposerVulnerabilitiesCommand;
use App\Security\VulnerabilityCheck;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

covers(CheckComposerVulnerabilitiesCommand::class);

beforeEach(function () {
    Storage::fake('security');
    Mail::fake();
    Process::fake(['*' => Process::result(output: '{"advisories":[]}')]);
});

it('runs the vulnerability check and reports success', function () {
    $exitCode = (new CheckComposerVulnerabilitiesCommand)->handle(app(VulnerabilityCheck::class));

    expect($exitCode)->toBe(Command::SUCCESS);
    // The check ran: it persisted (pruned) mute state on the security disk.
    Storage::disk('security')->assertExists('vulnerability-mutes.json');
});
