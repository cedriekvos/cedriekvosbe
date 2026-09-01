<?php

declare(strict_types=1);

namespace App\Blog\Storage;

use App\Blog\DraftSlug;
use Illuminate\Support\Facades\Storage;

final readonly class PostFileStorage
{
    private const string DISK = 'posts';

    private const string EXTENSION = '.md';

    public function __construct(
        private DraftSlug $draftSlug,
    ) {}

    /**
     * Slugs of every Markdown post on the disk.
     *
     * @return array<int, string>
     */
    public function all(): array
    {
        $slugs = [];

        foreach (Storage::disk(self::DISK)->files() as $path) {
            if (str_ends_with($path, self::EXTENSION)) {
                $slugs[] = basename($path, self::EXTENSION);
            }
        }

        return $slugs;
    }

    public function read(string $slug): string
    {
        return Storage::disk(self::DISK)->get($this->fileFor($slug)) ?? '';
    }

    public function exists(string $slug): bool
    {
        return Storage::disk(self::DISK)->exists($this->fileFor($slug));
    }

    public function put(string $slug, string $contents): void
    {
        Storage::disk(self::DISK)->put($this->fileFor($slug), $contents);
    }

    public function delete(string $slug): bool
    {
        if (! $this->exists($slug)) {
            return false;
        }

        return Storage::disk(self::DISK)->delete($this->fileFor($slug));
    }

    public function startsWithDraft(string $slug): bool
    {
        return $this->draftSlug->isDraft($slug);
    }

    public function getSlugFor(string $baseSlug, bool $isDraft): string
    {
        return $this->draftSlug->apply($baseSlug, $isDraft);
    }

    private function fileFor(string $slug): string
    {
        return $slug.self::EXTENSION;
    }
}
