<?php

declare(strict_types=1);

namespace App\Telegram\DTO;

final class Message
{
    public function __construct(
        public readonly int    $updateID,
        public readonly int    $fromUserID,
        public readonly int    $chatID,
        public readonly string $text,
    )
    {
    }

    public static function fromUpdate(array $update): self
    {
        return new self(
            updateID: $update['update_id'],
            fromUserID: $update['message']['from']['id'],
            chatID: $update['message']['chat']['id'],
            text: $update['message']['text'] ?? '',
        );
    }
}