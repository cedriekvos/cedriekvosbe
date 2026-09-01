<?php

declare(strict_types=1);

namespace App\About\Repositories;

use App\About\About;

final readonly class AboutRepository
{
    public function __construct(
        private AboutGetRepository $aboutGetRepository,
        private AboutWriteRepository $aboutWriteRepository,
    ) {}

    public function get(): About
    {
        return $this->aboutGetRepository->get();
    }

    public function save(string $heading, string $bio): void
    {
        $this->aboutWriteRepository->save($heading, $bio);
    }
}
