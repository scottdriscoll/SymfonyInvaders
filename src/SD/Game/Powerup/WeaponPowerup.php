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
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class WeaponPowerup extends AbstractPowerup
{
    /**
     * @var string
     */
    private $color = 'red';

    /**
     * @param OutputHelper $output
     */
    public function draw(OutputHelper $output)
    {
        $output->write(sprintf('<fg=%s>^</fg=%s>', $this->color, $this->color));
    }
    
    /**
     * @param OutputHelper $output
     * @param Player $player
     */
    public function drawActivated(OutputHelper $output, Player $player)
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
