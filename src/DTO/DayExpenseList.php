<?php

declare(strict_types=1);

namespace App\DTO;

final class DayExpenseList
{
    public function __construct(
        public readonly array $dayExpenses,
    )
    {
    }
}