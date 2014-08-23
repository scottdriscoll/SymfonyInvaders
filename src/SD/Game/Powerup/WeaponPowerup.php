<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Powerup;

use SD\ConsoleHelper\OutputHelper;
use SD\ConsoleHelper\ScreenBuffer;
use SD\Game\Player;

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
     * @param ScreenBuffer $output
     */
    public function draw(ScreenBuffer $output)
    {
        $output->putNextValue($this->xPosition, $this->yPosition, sprintf('<fg=%s>^</fg=%s>', $this->color, $this->color));
    }
    
    /**
     * @param ScreenBuffer $output
     * @param Player $player
     */
    public function drawActivated(ScreenBuffer $output, Player $player)
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
