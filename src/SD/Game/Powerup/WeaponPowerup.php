<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Powerup;

use SD\InvadersBundle\Helpers\OutputHelper;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class WeaponPowerup extends AbstractPowerup
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
        $output->write(sprintf('<fg=%s>^</fg=%s>', $this->color, $this->color));
    }
}
