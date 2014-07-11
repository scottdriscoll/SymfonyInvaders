<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\InvadersBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class AlienHitEvent extends Event
{
    /**
     * @var int
     */
    private $projectileIndex;

    /**
     * @param int $projectileIndex
     */
    public function __construct($projectileIndex)
    {
        $this->projectileIndex = $projectileIndex;
    }

    /**
     * @return int
     */
    public function getProjectileIndex()
    {
        return $this->projectileIndex;
    }
}
