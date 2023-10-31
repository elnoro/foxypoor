<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\MessageHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function usleep;

#[AsCommand(name: "app:bot")]
final class BotCommand extends Command
{
    public function __construct(
        private readonly MessageHandlerInterface $messageHandler,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Running...');

        while (true) {
            $output->writeln('Receiving updates...');
            $this->messageHandler->receiveUpdates();
            $output->writeln('Updates processed!');
        }
    }
}