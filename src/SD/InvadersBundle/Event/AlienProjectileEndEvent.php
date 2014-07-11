<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\InvadersBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Called when an alien projectile reaches the bottom of the screen
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class AlienProjectileEndEvent extends Event
{
    /**
     * @var int
     */
    private $xPosition;

    /**
     * @param int $xPosition
     */
    public function __construct($xPosition)
    {
        $this->xPosition = $xPosition;
    }

    /**
     * @return int
     */
    public function getXPosition()
    {
        return $this->xPosition;
    }
}
