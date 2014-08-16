<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Powerup;

use SD\InvadersBundle\Helpers\OutputHelper;
use SD\Game\Player;
use SD\Game\ScreenBuffer;

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
     * @param ScreenBuffer $output
     */
    public function draw(ScreenBuffer $output)
    {
        $output->putNextValue($this->xPosition, $this->yPosition, sprintf('<fg=%s>O</fg=%s>', $this->color, $this->color));
    }

    /**
     * @param ScreenBuffer $output
     * @param Player $player
     */
    public function drawActivated(ScreenBuffer $output, Player $player)
    {
        $player->addHeightLayer(1);
        $output->putNextValue($player->getXPosition(), $player->getYPosition() - $player->getHeight(), sprintf('<fg=%s>' . str_repeat('_', $player->getWidth()) . '</fg=%s>', $this->color, $this->color));        
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
