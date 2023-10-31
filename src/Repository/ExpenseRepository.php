<?php

namespace App\Repository;

use App\DTO\DayExpense;
use App\DTO\DayExpenseList;
use App\Entity\Expense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Clock\ClockInterface;

/**
 * @extends ServiceEntityRepository<Expense>
 *
 * @method Expense|null find($id, $lockMode = null, $lockVersion = null)
 * @method Expense|null findOneBy(array $criteria, array $orderBy = null)
 * @method Expense[]    findAll()
 * @method Expense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ExpenseRepository extends ServiceEntityRepository implements ExpenseReporter
{
    public function __construct(
        ManagerRegistry        $registry,
        private ClockInterface $clock,
    )
    {
        parent::__construct($registry, Expense::class);
    }

    public function getSummary(int $daysAgo): DayExpenseList
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = trim('
            select sum(amount) as totalExpense, date(spent_at) as expenseDate
            from expense
            where spent_at > :date
            group by date(spent_at)
            order by date(spent_at) asc
        ');

        $from = $this->clock->now()->modify(sprintf('-%d days', $daysAgo))->getTimestamp();
        $result = $conn->executeQuery($sql, ['date' => $from])->fetchAllAssociative();

        $dayExpenses = array_map(
            fn($row) => new DayExpense((int)$row['totalExpense'], (string)$row['expenseDate']),
            $result
        );

        return new DayExpenseList($dayExpenses);
    }
}
