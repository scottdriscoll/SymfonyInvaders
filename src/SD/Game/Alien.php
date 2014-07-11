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
    const STATE_DYING = 1;
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
     * When the alien is shot, change his color for a bit before he disappears
     *
     * @var double
     */
    private $stateChanged;

    /**
     * @var int
     */
    private $xPosition;

    /**
     * @var int
     */
    private $yPosition;

    public function __construct($xPosition, $yPosition, $fireChance, $velocity)
    {
        $this->xPosition = $xPosition;
        $this->yPosition = $yPosition;
        $this->fireChance = $fireChance;
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
     * @param float $stateChanged
     */
    public function setStateChanged($stateChanged)
    {
        $this->stateChanged = $stateChanged;
    }

    /**
     * @return float
     */
    public function getStateChanged()
    {
        return $this->stateChanged;
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
}
