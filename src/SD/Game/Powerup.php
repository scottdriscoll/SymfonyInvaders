<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class Powerup
{
    /**
     * @var int
     */
    private $xPosition;

    /**
     * @var int
     */
    private $yPosition;

    /**
     * @var int
     */
    private $lastUpdate = 0;

    /**
     * @param int $xPosition
     * @param int $yPosition
     */
    public function __construct($xPosition, $yPosition)
    {
        $this->xPosition = $xPosition;
        $this->yPosition = $yPosition;
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
     * @param int $lastUpdate
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;
    }

    /**
     * @return int
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }
}
