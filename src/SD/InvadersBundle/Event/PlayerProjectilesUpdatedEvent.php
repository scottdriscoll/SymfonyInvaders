<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\InvadersBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class PlayerProjectilesUpdatedEvent extends Event
{
    /**
     * @var array
     */
    private $projectiles;

    public function __construct(array $projectiles)
    {
        $this->projectiles = $projectiles;
    }

    /**
     * @return array
     */
    public function getProjectiles()
    {
        return $this->projectiles;
    }
}
