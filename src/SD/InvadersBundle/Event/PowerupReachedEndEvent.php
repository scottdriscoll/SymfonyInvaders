<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\InvadersBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use SD\Game\Powerup;

/**
 * Called when an alien projectile reaches the bottom of the screen
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class PowerupReachedEndEvent extends Event
{
    /**
     * @var Powerup
     */
    private $powerup;

    /**
     * @param Powerup $powerup
     */
    public function __construct(Powerup $powerup)
    {
        $this->powerup = $powerup;
    }

    /**
     * @return Powerup
     */
    public function getPowerup()
    {
        return $this->powerup;
    }
}
