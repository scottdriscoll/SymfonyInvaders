<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\InvadersBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use SD\Game\Powerup\AbstractPowerup;

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
