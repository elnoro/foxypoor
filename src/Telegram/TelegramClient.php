<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Telegram\DTO\Message;
use App\Telegram\DTO\MessageList;

final class TelegramClient
{
    public function __construct(
        private readonly string $token,
        private readonly int    $pollingTimeout,
    )
    {
    }

    public function sendMessage(int $chatId, string $message): void
    {
        $url = sprintf(
            "https://api.telegram.org/bot%s/sendMessage?chat_id=%d&text=%s",
            $this->token,
            $chatId,
            urlencode($message),
        );

        file_get_contents($url);
    }

    public function getUpdates(): MessageList
    {
        $url = sprintf(
            "https://api.telegram.org/bot%s/getUpdates?allowed_updates=[\"message\"]&timeout=%d",
            $this->token,
            $this->pollingTimeout,
        );

        $response = file_get_contents($url);
        $response = json_decode($response, true);

        $messages = [];

        foreach ($response['result'] as $message) {
            $messages[] = Message::fromUpdate($message);
        }

        return new MessageList($messages);
    }

    public function confirm(Message $message): void
    {
        $url = sprintf(
            "https://api.telegram.org/bot%s/getUpdates?allowed_updates=[\"message\"]&offset=%d",
            $this->token,
            $message->updateID + 1,
        );

        file_get_contents($url);
    }
}