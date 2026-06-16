<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Game\Powerup;

use App\Game\Player;
use App\Tui\GameFrame;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
abstract class AbstractPowerup
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
     */
    private $lastUpdate = 0;
    
    /**
     *
     * @var boolean
     */
    private $activated;

    /**
     * @param int $xPosition
     * @param int $yPosition
     */
    public function __construct($xPosition, $yPosition)
    {
        $this->xPosition = $xPosition;
        $this->yPosition = $yPosition;
        $this->activated = false;
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

    public function activate()
    {
        $this->activated = true;
    }
    
    /**
     * 
     * @return boolean
     */
    public function isActivated()
    {
        return $this->activated;
    }
    /**
     * @param GameFrame $output
     */
    abstract public function draw(GameFrame $output);

    /**
     * @param GameFrame $output
     * @param Player $player
     */
    abstract public function drawActivated(GameFrame $output, Player $player);    
    
    /**
     * @param Player $player
     */
    abstract public function applyUpgradeToPlayer(Player $player);

    /**
     * @param Player $player
     */
    abstract public function unApplyUpgradeToPlayer(Player $player);
    
    /**
     * @return boolean
     */
    abstract public function isLosable();

    /**
     * @return int
     */
    public function getPriority()
    {
        return 0;
    }
}
