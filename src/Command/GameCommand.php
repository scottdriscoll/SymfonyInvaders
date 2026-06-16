<?php

namespace App\Command;

use App\Tui\InvadersTuiRunner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'invaders:launch', description: 'Launch Symfony Invaders.')]
final class GameCommand extends Command
{
    public function __construct(
        private readonly InvadersTuiRunner $invadersTuiRunner,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->invadersTuiRunner->run();

        return Command::SUCCESS;
    }
}
