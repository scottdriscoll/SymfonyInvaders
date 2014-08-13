<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Powerup;

use SD\InvadersBundle\Helpers\OutputHelper;
use SD\Game\Player;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class ShieldPowerup extends AbstractPowerup
{
    /**
     * @var string
     */
    private $color = 'blue';

    /**
     * @param OutputHelper $output
     */
    public function draw(OutputHelper $output)
    {
        $output->write(sprintf('<fg=%s>O</fg=%s>', $this->color, $this->color));
    }

    /**
     * @param OutputHelper $output
     * @param Player $player
     */
    public function drawActivated(OutputHelper $output, Player $player)
    {
        // Reset cursor to a known position
        $output->moveCursorDown($player->getYPosition());
        $output->moveCursorFullLeft();

        // Move to proper location
        $output->moveCursorUp($player->getHeight() + 2);
        $output->moveCursorRight($player->getXPosition());
        $output->write(sprintf('<fg=%s>' . str_repeat('-', $player->getWidth()) . '</fg=%s>', $this->color, $this->color));
    }
    
    public function applyUpgradeToPlayer(Player $player)
    {
        if ($player->addShield()) {
            $this->activate();
        }
            
    }
    
    public function isLosable() 
    {
        return true;
    }
}
