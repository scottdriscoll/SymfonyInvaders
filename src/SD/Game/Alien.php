<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class Alien
{
    const DIRECTION_LEFT = 1;
    const DIRECTION_RIGHT = 2;
    const STATE_ALIVE = 1;
    const STATE_DYING = 2;
    const STATE_DEAD = 3;
    const DELAY_UNTIL_DEAD = 0.5;

    /**
     * @var double
     */
    private $fireChance;

    /**
     * @var double
     */
    private $velocity;

    /**
     * @var int
     */
    private $direction;

    /**
     * @var int
     */
    private $state;

    /**
     * @var double
     */
    private $lastUpdated = 0;

    /**
     * Prevent alien from spamming too many projectiles
     *
     * @var double
     */
    private $lastFired = 0;

    /**
     * @var double
     */
    private $fireDelay;

    /**
     * @var int
     */
    private $xPosition;

    /**
     * @var int
     */
    private $yPosition;

    /**
     * @param int $xPosition
     * @param int $yPosition
     * @param double $fireChance
     * @param double fireDelay
     * @param int $velocity
     */
    public function __construct($xPosition, $yPosition, $fireChance, $fireDelay, $velocity)
    {
        $this->xPosition = $xPosition;
        $this->yPosition = $yPosition;
        $this->fireChance = $fireChance;
        $this->fireDelay = $fireDelay;
        $this->velocity = $velocity;
        $this->direction = self::DIRECTION_RIGHT;
        $this->state = self::STATE_ALIVE;
    }

    /**
     * @param int $direction
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
    }

    /**
     * @return int
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param float $fireChance
     */
    public function setFireChance($fireChance)
    {
        $this->fireChance = $fireChance;
    }

    /**
     * @return float
     */
    public function getFireChance()
    {
        return $this->fireChance;
    }

    /**
     * @param int $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param float $lastUpdated
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
    }

    /**
     * @return float
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    /**
     * @param float $velocity
     */
    public function setVelocity($velocity)
    {
        $this->velocity = $velocity;
    }

    /**
     * @return float
     */
    public function getVelocity()
    {
        return $this->velocity;
    }

    /**
     * @param int $xPosition
     */
    public function setXPosition($xPosition)
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
     * @param float $fireDelay
     */
    public function setFireDelay($fireDelay)
    {
        $this->fireDelay = $fireDelay;
    }

    /**
     * @return float
     */
    public function getFireDelay()
    {
        return $this->fireDelay;
    }

    /**
     * @param float $lastFired
     */
    public function setLastFired($lastFired)
    {
        $this->lastFired = $lastFired;
    }

    /**
     * @return float
     */
    public function getLastFired()
    {
        return $this->lastFired;
    }
}
