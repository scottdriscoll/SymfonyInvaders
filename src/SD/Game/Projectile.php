<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class Projectile
{
    /**
     * @var int
     */
    protected $xPosition;

    /**
     * @var int
     */
    protected $yPosition;

    /**
     * @var int
     *
     * Timestamp when this projectile was last updated
     */
    protected $lastUpdatedTime;

    /**
     * @var int
     *
     * Elapsed time that must pass before this projectile will move
     */
    protected $velocity;

    /**
     * @param int $xPosition
     * @param int $yPosition
     * @param int $time
     * @param int $velocity
     */
    public function __construct($xPosition, $yPosition, $time, $velocity)
    {
        $this->xPosition = $xPosition;
        $this->yPosition = $yPosition;
        $this->lastUpdatedTime = $time;
        $this->velocity = $velocity;
    }

    /**
     * @param int $lastUpdatedTime
     */
    public function setLastUpdatedTime($lastUpdatedTime)
    {
        $this->lastUpdatedTime = $lastUpdatedTime;
    }

    /**
     * @return int
     */
    public function getLastUpdatedTime()
    {
        return $this->lastUpdatedTime;
    }

    /**
     * @param int $yPosition
     */
    public function setYPosition($yPosition)
    {
        $this->yPosition = $yPosition;
    }

    /**
     * @return int
     */
    public function getYPosition()
    {
        return $this->yPosition;
    }

    /**
     * @return int
     */
    public function getVelocity()
    {
        return $this->velocity;
    }
}
