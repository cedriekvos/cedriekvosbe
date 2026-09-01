<?php

declare(strict_types=1);

namespace App\Microblog\Repositories;

use App\Microblog\Markdown\MessageFileSerializer;
use App\Microblog\Message;
use App\Microblog\Storage\MessageFileStorage;
use Illuminate\Support\Carbon;
use RuntimeException;

final readonly class MessageWriteRepository
{
    public function __construct(
        private MessageFileStorage $messageFileStorage,
        private MessageFileSerializer $messageFileSerializer,
        private MessageSource $messageSource,
    ) {}

    /**
     * Create a new message on disk. Returns the generated id.
     */
    public function create(string $body): string
    {
        $id = $this->messageFileStorage->generateId();
        $postedAt = Carbon::now()->format('Y-m-d H:i:s');

        $this->writeFile($id, $postedAt, $body);

        return $id;
    }

    /**
     * Update an existing message's body. The original posted_at is preserved
     * so editing never moves a message in the newest-first order.
     */
    public function update(string $id, string $body): void
    {
        $message = $this->messageSource->find($id);

        if (! $message instanceof Message) {
            throw new RuntimeException("Message [{$id}] does not exist.");
        }

        $this->writeFile($id, $message->posted_at, $body);
    }

    public function delete(string $id): bool
    {
        return $this->messageFileStorage->delete($id);
    }

    private function writeFile(string $id, string $postedAt, string $body): void
    {
        $this->messageFileStorage->put($id, $this->messageFileSerializer->serialize($id, $postedAt, $body));
    }
}
