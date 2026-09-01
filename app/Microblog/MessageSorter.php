<?php

declare(strict_types=1);

namespace App\Microblog;

final readonly class MessageSorter
{
    /**
     * @param  array<int, Message>  $messages
     * @return array<int, Message>
     */
    public function sortByPostedAtDescending(array $messages): array
    {
        usort($messages, $this->compareNewestFirst(...));

        return $messages;
    }

    /**
     * Compare two messages for a newest-first ordering.
     *
     * Messages are ranked by posted_at (most recent first). When two messages
     * share the same posted_at, the tie is broken by id (ULID) in descending
     * order, since a higher ULID was generated later.
     *
     * @return int negative when $a comes first, positive when $b comes first, 0 when equal
     */
    private function compareNewestFirst(Message $a, Message $b): int
    {
        $timestampA = strtotime($a->posted_at) ?: 0;
        $timestampB = strtotime($b->posted_at) ?: 0;

        if ($timestampA !== $timestampB) {
            return $timestampB <=> $timestampA;
        }

        return $b->id <=> $a->id;
    }
}
