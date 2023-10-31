<?php

declare(strict_types=1);

namespace App\Telegram\DTO;

final class MessageList
{
    /**
     * @param Message[] $messages
     */
    public function __construct(
        public array $messages,
    )
    {
        $this->sortByUpdateID();
    }

    private function sortByUpdateID(): void
    {
        usort($this->messages, function (Message $a, Message $b) {
            return $a->updateID <=> $b->updateID;
        });
    }
}
