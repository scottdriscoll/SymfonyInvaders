<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Game\Projectile;

use App\Tui\GameFrame;

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
     * @param GameFrame $output
     */
    public function draw(GameFrame $output)
    {
        $output->putNextValue($this->xPosition, $this->yPosition, '|', $this->color);
    }
}
