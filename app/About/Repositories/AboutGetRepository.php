<?php

declare(strict_types=1);

namespace App\About\Repositories;

use App\About\About;
use App\About\AboutFactory;
use App\About\Markdown\AboutFileParser;
use App\About\Storage\AboutFileStorage;

final readonly class AboutGetRepository
{
    public function __construct(
        private AboutFileStorage $aboutFileStorage,
        private AboutFileParser $aboutFileParser,
        private AboutFactory $aboutFactory,
    ) {}

    public function get(): About
    {
        return $this->aboutFactory->make(
            $this->aboutFileParser->parse($this->aboutFileStorage->read()),
        );
    }
}
