<?php

declare(strict_types=1);

namespace App\Microblog;

use App\Microblog\Markdown\MessageTextToHtmlConverter;

final readonly class MessageFactory
{
    public function __construct(
        private MessageTextToHtmlConverter $messageTextToHtmlConverter,
    ) {}

    /**
     * Build a Message from parsed front-matter/body data. Missing or non-string
     * fields default to an empty string; the body is carried both as the raw
     * text the author typed and as the HTML the reader sees.
     *
     * @param  array<string, mixed>  $data
     */
    public function make(array $data): Message
    {
        $body = $this->stringOrEmpty($data['body'] ?? null);

        return new Message(
            id: $this->stringOrEmpty($data['id'] ?? null),
            body: $body,
            body_as_html: $this->messageTextToHtmlConverter->convert($body),
            posted_at: $this->stringOrEmpty($data['posted_at'] ?? null),
        );
    }

    private function stringOrEmpty(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
