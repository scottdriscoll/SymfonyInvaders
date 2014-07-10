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
     * @param int $currentHealth
     * @param int $maximumHealth
     */
    public function __construct($currentHealth, $maximumHealth)
    {
        $this->currentHealth = $currentHealth;
        $this->maximumHealth = $maximumHealth;
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
}
