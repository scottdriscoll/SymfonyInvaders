<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\InvadersBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class AlienDeadEvent extends Event
{
    /**
     * @var int
     */
    private $totalAliens;

    /**
     * @var int
     */
    private $aliveAliens;

    /**
     * @param int $totalAliens
     * @param int $aliveAliens
     */
    public function __construct($totalAliens, $aliveAliens)
    {
        $this->totalAliens = $totalAliens;
        $this->aliveAliens = $aliveAliens;
    }

    /**
     * @return int
     */
    public function getAliveAliens()
    {
        return $this->aliveAliens;
    }

    /**
     * @return int
     */
    public function getTotalAliens()
    {
        return $this->totalAliens;
    }
}
