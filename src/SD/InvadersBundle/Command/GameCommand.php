<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\InvadersBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SD\ConsoleHelper\OutputHelper;
use SD\Game\Board as GameBoard;
use SD\Game\Engine as GameEngine;
use SD\Game\AlienManager;

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
        $outputHelper = new OutputHelper($output);
        $outputHelper->disableKeyboardOutput();
        $outputHelper->hideCursor();

        // Initialize Game board
        /** @var GameBoard $gameBoard */
        $gameBoard = $this->getContainer()->get('game.board');
        $gameBoard->setMessage('Arrow keys to move, space to shoot.');
        $gameBoard->initialize($outputHelper);

        // Initialize Aliens
        /** @var AlienManager $alienManager */
        $alienManager = $this->getContainer()->get('game.alien.manager');
        $alienManager->initialize();

        // Launch game
        /** @var GameEngine $engine */
        $engine = $this->getContainer()->get('game.engine');
        $engine->run();
    }
}
