<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use JMS\DiExtraBundle\Annotation as DI;
use SD\Game\Keyboard;
use SD\Game\Player;

/**
 * @DI\Service("game.engine")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class Engine
{
    /**
     * @var int
     */
    const HEARTBEAT_DURATION = 8000;

    /**
     * @var Keyboard
     */
    private $keyboard;

    /**
     * @var Player
     */
    private $player;

    /**
     * @DI\InjectParams({
     *     "player" = @DI\Inject("game.player"),
     *     "keyboard" = @DI\Inject("game.keyboard")
     * })
     *
     * @param Player $player
     * @param Keyboard $keyboard
     */
    public function __construct(Player $player, Keyboard $keyboard)
    {
        $this->player = $player;
        $this->keyboard = $keyboard;
    }

    public function run()
    {
        while (1) {
            $this->keyboard->processKeyboardEvents();


            usleep(self::HEARTBEAT_DURATION);
        }
    }
}
