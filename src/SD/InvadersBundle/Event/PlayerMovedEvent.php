<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\InvadersBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class PlayerMovedEvent extends Event
{
    /**
     * @var int
     */
    private $currentHealth;

    /**
     * @var int
     */
    private $maximumHealth;

    /**
     * @var int
     */
    private $currentXPosition;

    /**
     * @param int $currentHealth
     * @param int $maximumHealth
     * @param int $currentXPosition
     */
    public function __construct($currentHealth, $maximumHealth, $currentXPosition)
    {
        $this->currentHealth = $currentHealth;
        $this->maximumHealth = $maximumHealth;
        $this->currentXPosition = $currentXPosition;
    }

    /**
     * @return int
     */
    public function getCurrentHealth()
    {
        return $this->currentHealth;
    }

    /**
     * @return int
     */
    public function getMaximumHealth()
    {
        return $this->maximumHealth;
    }

    /**
     * @return int
     */
    public function getCurrentXPosition()
    {
        return $this->currentXPosition;
    }
}
