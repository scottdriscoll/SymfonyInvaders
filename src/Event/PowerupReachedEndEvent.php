<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\Game\Powerup\AbstractPowerup;

/**
 * Called when an alien projectile reaches the bottom of the screen
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class PowerupReachedEndEvent extends Event
{
    /**
     * @var AbstractPowerup
     */
    private $powerup;

    /**
     * @param AbstractPowerup $powerup
     */
    public function __construct(AbstractPowerup $powerup)
    {
        $this->powerup = $powerup;
    }

    /**
     * @return AbstractPowerup
     */
    public function getPowerup()
    {
        return $this->powerup;
    }
}
