<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Projectile;

use SD\InvadersBundle\Helpers\OutputHelper;
use SD\Game\ScreenBuffer;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
abstract class AbstractProjectile
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
     * @var double
     *
     * Timestamp when this projectile was last updated
     */
    protected $lastUpdatedTime;

    /**
     * @var double
     *
     * Elapsed time that must pass before this projectile will move
     */
    protected $velocity;

    /**
     * @param int $xPosition
     * @param int $yPosition
     * @param double $time
     * @param double $velocity
     */
    public function __construct($xPosition, $yPosition, $time, $velocity)
    {
        $this->xPosition = $xPosition;
        $this->yPosition = $yPosition;
        $this->lastUpdatedTime = $time;
        $this->velocity = $velocity;
    }

    /**
     * @param double $lastUpdatedTime
     */
    public function setLastUpdatedTime($lastUpdatedTime)
    {
        $this->lastUpdatedTime = $lastUpdatedTime;
    }

    /**
     * @return double
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
     * @return double
     */
    public function getXPosition()
    {
        return $this->xPosition;
    }

    /**
     * @return double
     */
    public function getVelocity()
    {
        return $this->velocity;
    }

    /**
     * @param ScreenBuffer $output
     */
    abstract public function draw(ScreenBuffer $output);
}
