<?php

declare(strict_types=1);

namespace App\Microblog\Repositories;

use App\Microblog\Markdown\MessageFileParser;
use App\Microblog\Message;
use App\Microblog\MessageFactory;
use App\Microblog\Storage\MessageFileStorage;

final readonly class MessageSource
{
    public function __construct(
        private MessageFileStorage $messageFileStorage,
        private MessageFileParser $messageFileParser,
        private MessageFactory $messageFactory,
    ) {}

    /**
     * @return array<int, Message>
     */
    public function all(): array
    {
        return array_map($this->toMessage(...), $this->messageFileStorage->all());
    }

    public function find(string $id): ?Message
    {
        return $this->messageFileStorage->exists($id) ? $this->toMessage($id) : null;
    }

    public function exists(string $id): bool
    {
        return $this->messageFileStorage->exists($id);
    }

    private function toMessage(string $id): Message
    {
        $data = $this->messageFileParser->parse($this->messageFileStorage->read($id), $id);

        return $this->messageFactory->make($data);
    }
}
