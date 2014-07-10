<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\InvadersBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SD\Game\Board as GameBoard;
use SD\Game\Engine as GameEngine;
use SD\Game\Player;

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
        $screenWidth = 100;
        $screenHeight = 40;

        // Disable output for keystrokes
        shell_exec('stty -icanon -echo');

        // Initialize Gameboard
        /** @var GameBoard $gameBoard */
        $gameBoard = $this->getContainer()->get('game.board');
        $gameBoard->setMessage('Arrow keys to move, space to shoot.');
        $gameBoard->draw($output, $screenWidth, $screenHeight);

        // Initialize Player
        /** @var Player $player */
        $player = $this->getContainer()->get('game.player');
        $player->initialize(0, $screenWidth - 3, (int) ($screenWidth / 2), $screenHeight - 2);

        // Initialize Aliens

        // Launch game
        /** @var GameEngine $engine */
        $engine = $this->getContainer()->get('game.engine');
        $engine->run();
    }
}
