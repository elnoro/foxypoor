<?php

declare(strict_types=1);

namespace App\Intention\DTO;

enum IntentionType: string
{
    case AddExpense = 'addExpense';
    case Cancel = 'cancel';
    case ShowExpenses = 'showExpenses';
}