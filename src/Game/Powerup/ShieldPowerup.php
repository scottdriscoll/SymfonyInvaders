<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Game\Powerup;

use App\Game\Player;
use App\Tui\GameFrame;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class ShieldPowerup extends AbstractPowerup
{
    /**
     * @var string
     */
    private $color = 'blue';

    /**
     * @param GameFrame $output
     */
    public function draw(GameFrame $output)
    {
        $output->putNextValue($this->xPosition, $this->yPosition, 'O', $this->color);
    }

    /**
     * @param GameFrame $output
     * @param Player $player
     */
    public function drawActivated(GameFrame $output, Player $player)
    {
        $player->addHeightLayer(1);
        $output->putArrayOfValues($player->getXPosition(), $player->getYPosition() - $player->getHeight(), array(str_pad('', $player->getWidth(), '_')), $this->color);
    }

    /**
     * @param Player $player
     */
    public function applyUpgradeToPlayer(Player $player)
    {
        if ($player->addShield()) {
            $this->activate();
        }
            
    }

    /**
     * @param Player $player
     */
    public function unApplyUpgradeToPlayer(Player $player)
    {
        $player->removeShield();
    }

    /**
     * @return bool
     */
    public function isLosable() 
    {
        return true;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return 5;
    }    
}
