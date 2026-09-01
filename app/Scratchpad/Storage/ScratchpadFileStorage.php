<?php

declare(strict_types=1);

namespace App\Scratchpad\Storage;

use Illuminate\Support\Facades\Storage;

final readonly class ScratchpadFileStorage
{
    private const string DISK = 'meta';

    private const string PATH = 'scratchpad.md';

    public function read(): string
    {
        return Storage::disk(self::DISK)->get(self::PATH) ?? '';
    }

    public function write(string $contents): void
    {
        Storage::disk(self::DISK)->put(self::PATH, $contents);
    }
}
