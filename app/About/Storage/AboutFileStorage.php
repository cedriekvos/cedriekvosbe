<?php

declare(strict_types=1);

namespace App\About\Storage;

use Illuminate\Support\Facades\Storage;

final readonly class AboutFileStorage
{
    private const string DISK = 'meta';

    private const string PATH = 'about.yaml';

    public function read(): string
    {
        return Storage::disk(self::DISK)->get(self::PATH) ?? '';
    }

    public function write(string $contents): void
    {
        Storage::disk(self::DISK)->put(self::PATH, $contents);
    }
}
