<?php

declare(strict_types=1);

namespace App\Microblog\Repositories;

use App\Microblog\Message;
use App\Microblog\MessageSorter;

final readonly class MessageGetRepository
{
    public function __construct(
        private MessageSource $messageSource,
        private MessageSorter $messageSorter,
    ) {}

    /**
     * @return array<int, Message>
     */
    public function getAllSortedByPostedAtDescending(): array
    {
        return $this->messageSorter->sortByPostedAtDescending($this->messageSource->all());
    }

    public function find(string $id): ?Message
    {
        return $this->messageSource->find($id);
    }

    public function exists(string $id): bool
    {
        return $this->messageSource->exists($id);
    }
}
