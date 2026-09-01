<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Security\VulnerabilityCheck;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Audit installed Composer packages and email the maintainer about new vulnerabilities.')]
#[Signature('security:check-vulnerabilities')]
final class CheckComposerVulnerabilitiesCommand extends Command
{
    public function handle(VulnerabilityCheck $vulnerabilityCheck): int
    {
        $vulnerabilityCheck->run();

        return self::SUCCESS;
    }
}
