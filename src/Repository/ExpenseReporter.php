<?php

declare(strict_types=1);

namespace App\Repository;


use App\DTO\DayExpenseList;
use App\Entity\Expense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

interface ExpenseReporter
{
    public function getSummary(int $daysAgo): DayExpenseList;
}