<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Powerup;

use SD\InvadersBundle\Helpers\OutputHelper;
use SD\Game\Player;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
abstract class AbstractPowerup
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

    /**
     * @param OutputHelper $output
     */
    abstract public function draw(OutputHelper $output);
    
    abstract public function applyUpgradeToPlayer(Player $player);
}
