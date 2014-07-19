<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\InvadersBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class BossHitEvent extends Event
{
    /**
     * @var int
     */
    private $health;

    /**
     * @var int
     */
    private $projectileIndex;

    /**
     * @param int $health
     * @param int $projectileIndex
     */
    public function __construct($health, $projectileIndex)
    {
        $this->health = $health;
        $this->projectileIndex = $projectileIndex;
    }

    /**
     * @return int
     */
    public function getHealth()
    {
        return $this->health;
    }

    /**
     * @return int
     */
    public function getProjectileIndex()
    {
        return $this->projectileIndex;
    }
}
