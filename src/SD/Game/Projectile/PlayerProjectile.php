<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Projectile;

use SD\InvadersBundle\Helpers\OutputHelper;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class PlayerProjectile extends AbstractProjectile
{
    /**
     * @var string
     */
    private $color = 'red';

    /**
     * @param OutputHelper $output
     */
    public function draw(OutputHelper $output)
    {
        $output->write(sprintf('<fg=%s>|</fg=%s>', $this->color, $this->color));
    }
}
