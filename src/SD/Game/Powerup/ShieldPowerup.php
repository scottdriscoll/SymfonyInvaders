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
        $player->addHeightLayer(1);
        $output->moveCursorRight($player->getXPosition());
        $output->write(sprintf('<fg=%s>' . str_repeat('_', $player->getWidth()) . '</fg=%s>', $this->color, $this->color));
        
    }

    /**
     * @param Player $player
     */
    public function applyUpgradeToPlayer(Player $player)
    {
        if ($player->addShield()) {
            $this->activate();
        }
            
    }

    /**
     * @param Player $player
     */
    public function unApplyUpgradeToPlayer(Player $player)
    {
        $player->removeShield();
    }

    /**
     * @return bool
     */
    public function isLosable() 
    {
        return true;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return 5;
    }    
}
