<?php

declare(strict_types=1);

namespace App\Service;

interface MessageHandlerInterface
{
    public function receiveUpdates(): void;
}