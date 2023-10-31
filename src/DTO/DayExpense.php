<?php

declare(strict_types=1);

namespace App\DTO;

use DateTimeImmutable;

final class DayExpense
{
    public function __construct(
        private readonly int   $amount,
        public readonly string $spentAt,
    )
    {
    }

    public function getTotal(): string
    {
        return number_format($this->amount / 100, 2);
    }
}