<?php

declare(strict_types=1);

namespace App\Security\Storage;

use Illuminate\Support\Facades\Storage;

final readonly class MuteFileStorage
{
    private const string DISK = 'security';

    private const string PATH = 'vulnerability-mutes.json';

    public function read(): string
    {
        return Storage::disk(self::DISK)->get(self::PATH) ?? '';
    }

    public function write(string $contents): void
    {
        Storage::disk(self::DISK)->put(self::PATH, $contents);
    }
}
