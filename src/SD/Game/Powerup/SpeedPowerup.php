<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Powerup;

use SD\InvadersBundle\Helpers\OutputHelper;
use SD\Game\Player;
use SD\InvadersBundle\Events;
use SD\InvadersBundle\Event\PowerupActivatedEvent;
use SD\Game\ScreenBuffer;
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
     * @param ScreenBuffer $output
     */
    public function draw(ScreenBuffer $output)
    {
        $output->putNextValue($this->xPosition, $this->yPosition,'$', $this->color);
    }
    
    /**
     * @param ScreenBuffer $output
     * @param Player $player
     */
    public function drawActivated(ScreenBuffer $output, Player $player)
    {
        $output->putNextValue($player->getXPosition() - 1, $player->getYPosition() - 1, '<', $this->color);
        $output->putNextValue($player->getXPosition() + $player->getWidth(), $player->getYPosition() - 1, '>', $this->color);
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
