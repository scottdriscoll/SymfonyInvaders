<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\Game\Powerup\AbstractPowerup;
use App\Game\Player;

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
