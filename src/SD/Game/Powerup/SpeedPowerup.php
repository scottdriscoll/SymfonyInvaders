<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Powerup;

use SD\InvadersBundle\Helpers\OutputHelper;
use SD\Game\Player;
use SD\InvadersBundle\Events;
use SD\InvadersBundle\Event\PowerupActivatedEvent;
/**
 * @author Richard Bunce <richard.bunce@opensoftdev.com>
 */
class SpeedPowerup extends AbstractPowerup
{
    /**
     * @var string
     */
    private $color = 'green';

    /**
     * @param OutputHelper $output
     */
    public function draw(OutputHelper $output)
    {
        $output->write(sprintf('<fg=%s>$</fg=%s>', $this->color, $this->color));
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
        $output->moveCursorUp(2);
        $output->moveCursorRight($player->getXPosition() - 1);
        $output->write(sprintf('<fg=%s><</fg=%s>', $this->color, $this->color));
        $output->moveCursorRight($player->getWidth());
        $output->write(sprintf('<fg=%s>></fg=%s>', $this->color, $this->color));
    }

    /**
     * @param Player $player
     */
    public function applyUpgradeToPlayer(Player $player)
    {
        if ($player->addSpeed()) {
            $this->activate();
        }
    }

    /**
     * @param Player $player
     */
    public function unApplyUpgradeToPlayer(Player $player)
    {
        
    }

    /**
     * @return bool
     */
    public function isLosable() 
    {
        return false;
    }    
}
