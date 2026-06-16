<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Game\Powerup;

use App\Game\Player;
use App\Tui\GameFrame;

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
     * @param GameFrame $output
     */
    public function draw(GameFrame $output)
    {
        $output->putNextValue($this->xPosition, $this->yPosition,'$', $this->color);
    }
    
    /**
     * @param GameFrame $output
     * @param Player $player
     */
    public function drawActivated(GameFrame $output, Player $player)
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
