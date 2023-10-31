<?php

declare(strict_types=1);

namespace App\Command;


use App\Entity\Expense;
use App\Repository\ExpenseReporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function sprintf;

#[AsCommand(name: "app:dump-expenses")]
final class DumpExpensesCommand extends Command
{
    public function __construct(
        private ExpenseReporter $expenseReporter,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dayExpenseList = $this->expenseReporter->getSummary(10);

        foreach ($dayExpenseList->dayExpenses as $dayExpense) {
            $output->writeln(sprintf(
                "%s: %s EUR",
                $dayExpense->spentAt,
                $dayExpense->getTotal(),
            ));
        }

        return self::SUCCESS;
    }
}