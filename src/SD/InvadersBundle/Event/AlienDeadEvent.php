<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\InvadersBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use SD\Game\Alien;

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
     * @var Alien
     */
    private $alien;

    /**
     * @param int $totalAliens
     * @param int $aliveAliens
     * @param Alien $alien
     */
    public function __construct($totalAliens, $aliveAliens, Alien $alien)
    {
        $this->totalAliens = $totalAliens;
        $this->aliveAliens = $aliveAliens;
        $this->alien = $alien;
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

    /**
     * @return Alien
     */
    public function getAlien()
    {
        return $this->alien;
    }
}
