<?php

declare(strict_types=1);

namespace App\Security\Repositories;

final readonly class MuteStateRepository
{
    public function __construct(
        private MuteStateGetRepository $muteStateGetRepository,
        private MuteStateWriteRepository $muteStateWriteRepository,
    ) {}

    /**
     * @return array<string, string>
     */
    public function get(): array
    {
        return $this->muteStateGetRepository->get();
    }

    /**
     * @param  array<string, string>  $state
     */
    public function save(array $state): void
    {
        $this->muteStateWriteRepository->save($state);
    }
}
