<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Projectile;

use SD\ConsoleHelper\OutputHelper;
use SD\ConsoleHelper\ScreenBuffer;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class BossProjectile extends AbstractProjectile
{
    /**
     * @var string
     */
    private $color = 'yellow';

    /**
     * @param ScreenBuffer $output
     */
    public function draw(ScreenBuffer $output)
    {
        $output->putNextValue($this->xPosition, $this->yPosition, '|', $this->color);
    }
}
