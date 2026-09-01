<?php

declare(strict_types=1);

namespace App\Microblog\Repositories;

use App\Microblog\Message;

final readonly class MessagesRepository
{
    public function __construct(
        private MessageGetRepository $messageGetRepository,
        private MessageWriteRepository $messageWriteRepository,
    ) {}

    /**
     * Every message, sorted by posted_at descending.
     *
     * @return array<int, Message>
     */
    public function all(): array
    {
        return $this->messageGetRepository->getAllSortedByPostedAtDescending();
    }

    public function find(string $id): ?Message
    {
        return $this->messageGetRepository->find($id);
    }

    public function exists(string $id): bool
    {
        return $this->messageGetRepository->exists($id);
    }

    /**
     * Create a new message. Returns the generated id.
     */
    public function create(string $body): string
    {
        return $this->messageWriteRepository->create($body);
    }

    /**
     * Update an existing message's body, preserving its original posted_at.
     */
    public function update(string $id, string $body): void
    {
        $this->messageWriteRepository->update($id, $body);
    }

    public function delete(string $id): bool
    {
        return $this->messageWriteRepository->delete($id);
    }
}
