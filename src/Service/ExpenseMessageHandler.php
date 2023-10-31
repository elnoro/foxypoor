<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Expense;
use App\Intention\DTO\IntentionType;
use App\Intention\Exception\IntentionParserException;
use App\Intention\IntentionExtractor;
use App\Repository\ExpenseReporter;
use App\Telegram\DTO\Message;
use App\Telegram\TelegramClient;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;

use function mb_strlen;
use function sprintf;

final class ExpenseMessageHandler implements MessageHandlerInterface
{
    private const MAX_MESSAGE_LENGTH = 100;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ExpenseReporter        $expenseReporter,
        private readonly TelegramClient         $telegramClient,
        private readonly ClockInterface         $clock,
        private readonly LoggerInterface        $logger,
        private readonly IntentionExtractor     $intentionExtractor,
        private readonly int                    $userID,
        private readonly string                 $timezone,
    )
    {
    }

    public function receiveUpdates(): void
    {
        $updates = $this->telegramClient->getUpdates();
        foreach ($updates->messages as $message) {
            $this->tryProcessMessage($message);
        }
    }

    private function tryProcessMessage(Message $message): void
    {
        try {
            $this->telegramClient->confirm($message);
            $this->processMessage($message);
        } catch (\Throwable $e) {
            $this->logger->error('Error processing message: {exception}', ['exception' => $e]);
            $this->telegramClient->sendMessage($message->chatID, 'Error processing message!');
        }
    }

    private function processMessage(Message $message): void
    {
        if ($message->fromUserID !== $this->userID) {
            $this->logger->error(sprintf('Message from unknown user! %d', $message->fromUserID));

            return;
        }

        if (mb_strlen($message->text) > self::MAX_MESSAGE_LENGTH) {
            $this->telegramClient->sendMessage($message->chatID, 'Message too long!');

            return;
        }

        try {
            $intention = $this->intentionExtractor->parse($message->text);
        } catch (IntentionParserException $e) {
            $this->telegramClient->sendMessage($message->chatID, $e->getMessage());

            return;
        }

        if ($intention->type === IntentionType::ShowExpenses) {
            $dayExpenses = $this->expenseReporter->getSummary(10);
            $text = "Summary for the last 10 days:\n";
            foreach ($dayExpenses->dayExpenses as $dayExpense) {
                $text .= sprintf(
                    "%s: %s EUR\n",
                    $dayExpense->spentAt,
                    $dayExpense->getTotal(),
                );
            }

            $this->telegramClient->sendMessage($message->chatID, $text);

            return;
        }

        if ($intention->type === IntentionType::Cancel) {
            $expense = $this->entityManager->getRepository(Expense::class)->findOneBy([], ['id' => 'DESC']);
            if ($expense === null) {
                $this->telegramClient->sendMessage($message->chatID, 'No expenses to undo!');

                return;
            }

            $this->entityManager->remove($expense);
            $this->entityManager->flush();

            $this->telegramClient->sendMessage($message->chatID, 'Expense removed!');

            return;
        }

        if ($intention->type === IntentionType::AddExpense) {
            $expense = new Expense();
            $expense->setAmount($intention->amount);
            $now = $this->clock->withTimeZone($this->timezone)->now();
            $expense->setCreatedAt($now);
            $expense->setSpentAt($now);

            $this->entityManager->persist($expense);
            $this->entityManager->flush();

            $this->telegramClient->sendMessage($message->chatID, 'Expense added!');

            return;
        }

        $this->telegramClient->sendMessage($message->chatID, 'Unknown command!');
    }
}