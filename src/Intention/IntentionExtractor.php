<?php

declare(strict_types=1);

namespace App\Intention;

use App\Intention\DTO\Intention;

interface IntentionExtractor
{
    public function parse(string $message): Intention;
}