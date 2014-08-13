<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\InvadersBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use SD\Game\Powerup\AbstractPowerup;
use SD\Game\Player;

/**
 * Called when a powerup is activated
 *
 * @author Richard Bunce <richard.bunce@opensoftdev.com>
 */
class PowerupActivatedEvent extends Event
{
    /**
     * @var AbstractPowerup
     */
    private $powerup;
    
    /**
     * @var Player
     */
    private $player;

    /**
     * @param AbstractPowerup $powerup
     */
    public function __construct(AbstractPowerup $powerup, Player $player)
    {
        $this->powerup = $powerup;
        $this->player = $player;
    }

    /**
     * @return AbstractPowerup
     */
    public function getPowerup()
    {
        return $this->powerup;
    }
    
    /**
     * @return Player
     */
    public function getPlayer()
    {
        return $this->player;
    }    
}
