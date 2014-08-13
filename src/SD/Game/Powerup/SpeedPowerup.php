<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Powerup;

use SD\InvadersBundle\Helpers\OutputHelper;
use SD\Game\Player;

/**
 * @author Richard Bunce <richard.bunce@opensoftdev.com>
 */
class SpeedPowerup extends AbstractPowerup
{
    /**
     * @var string
     */
    private $color = 'green';

    /**
     * @param OutputHelper $output
     */
    public function draw(OutputHelper $output)
    {
        $output->write(sprintf('<fg=%s>$</fg=%s>', $this->color, $this->color));
    }
    
    public function applyUpgradeToPlayer(Player $player)
    {
        $player->addSpeed();
    }    
}
