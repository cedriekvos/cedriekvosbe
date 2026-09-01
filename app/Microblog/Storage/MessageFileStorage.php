<?php

declare(strict_types=1);

namespace App\Microblog\Storage;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final readonly class MessageFileStorage
{
    private const string DISK = 'microblog';

    private const string EXTENSION = '.md';

    /**
     * Ids of every Markdown message on the disk.
     *
     * @return array<int, string>
     */
    public function all(): array
    {
        $ids = [];

        foreach (Storage::disk(self::DISK)->files() as $path) {
            if (str_ends_with($path, self::EXTENSION)) {
                $ids[] = basename($path, self::EXTENSION);
            }
        }

        return $ids;
    }

    public function read(string $id): string
    {
        return Storage::disk(self::DISK)->get($this->fileFor($id)) ?? '';
    }

    public function exists(string $id): bool
    {
        return Storage::disk(self::DISK)->exists($this->fileFor($id));
    }

    public function put(string $id, string $contents): void
    {
        Storage::disk(self::DISK)->put($this->fileFor($id), $contents);
    }

    public function delete(string $id): bool
    {
        if (! $this->exists($id)) {
            return false;
        }

        return Storage::disk(self::DISK)->delete($this->fileFor($id));
    }

    public function generateId(): string
    {
        return (string) Str::ulid();
    }

    private function fileFor(string $id): string
    {
        return $id.self::EXTENSION;
    }
}
