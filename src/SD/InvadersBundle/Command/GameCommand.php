<?php

namespace SD\InvadersBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SD\Game\Board as GameBoard;
use SD\Game\Keyboard;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class GameCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('invaders:launch');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Disable output for keypresses
        shell_exec('stty -icanon -echo');

        /** @var GameBoard $gameBoard */
        $gameBoard = $this->getContainer()->get('game.board');
        $gameBoard->setMessage('Arrow keys to move, space to shoot.');
        $gameBoard->draw($output, 100, 40);

        /** @var Keyboard $keyboard */
        $keyboard = $this->getContainer()->get('game.keyboard');
        $keyboard->listenAndFireEvents();
    }
}
