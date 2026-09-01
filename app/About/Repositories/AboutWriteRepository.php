<?php

declare(strict_types=1);

namespace App\About\Repositories;

use App\About\Markdown\AboutFileSerializer;
use App\About\Storage\AboutFileStorage;

final readonly class AboutWriteRepository
{
    public function __construct(
        private AboutFileStorage $aboutFileStorage,
        private AboutFileSerializer $aboutFileSerializer,
    ) {}

    public function save(string $heading, string $bio): void
    {
        $this->aboutFileStorage->write($this->aboutFileSerializer->serialize($heading, $bio));
    }
}
