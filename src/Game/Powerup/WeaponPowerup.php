<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Game\Powerup;

use App\Game\Player;
use App\Tui\GameFrame;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class WeaponPowerup extends AbstractPowerup
{
    /**
     * @var string
     */
    private $color = 'red';

    /**
     * @param GameFrame $output
     */
    public function draw(GameFrame $output)
    {
        $output->putNextValue($this->xPosition, $this->yPosition, '^', $this->color);
    }
    
    /**
     * @param GameFrame $output
     * @param Player $player
     */
    public function drawActivated(GameFrame $output, Player $player)
    {

    }

    /**
     * @param Player $player
     */
    public function applyUpgradeToPlayer(Player $player)
    {
        if ($player->addWeapon()) {
            $this->activate();            
        }
    }

    /**
     * @param Player $player
     */
    public function unApplyUpgradeToPlayer(Player $player)
    {
       $player->removeWeapon();
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
        return 4;
    }      
}
