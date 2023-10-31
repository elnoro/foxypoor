<?php

declare(strict_types=1);

namespace App\Intention\DTO;

final class Intention
{
    public function __construct(
        public readonly IntentionType $type,
        public readonly int $amount,
    ) {
    }
}