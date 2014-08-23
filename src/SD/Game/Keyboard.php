<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\InvadersBundle\Events;
use SD\InvadersBundle\Event\PlayerMoveLeftEvent;
use SD\InvadersBundle\Event\PlayerMoveRightEvent;
use SD\InvadersBundle\Event\PlayerFireEvent;
use SD\InvadersBundle\Event\HeartbeatEvent;
use SD\ConsoleHelper\Keyboard as KeyboardHelper;

/**
 * @DI\Service("game.keyboard")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class Keyboard
{
    /**
     * @var string
     */
    const FIRE_KEY = ' ';

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var KeyboardHelper
     */
    private $keyboardHelper;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "keyboardHelper" = @DI\Inject("keyboard_helper")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param KeyboardHelper $keyboardHelper
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, KeyboardHelper $keyboardHelper)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->keyboardHelper = $keyboardHelper;
    }

    /**
     * @DI\Observe(Events::HEARTBEAT, priority = 0)
     *
     * @param HeartbeatEvent $event
     */
    public function processKeyboardEvents(HeartbeatEvent $event)
    {
        if (($key = $this->keyboardHelper->readKey()) !== null) {
            switch ($key) {
                case KeyboardHelper::LEFT_ARROW:
                    $this->eventDispatcher->dispatch(Events::PLAYER_MOVE_LEFT, new PlayerMoveLeftEvent());
                    break;

                case KeyboardHelper::RIGHT_ARROW:
                    $this->eventDispatcher->dispatch(Events::PLAYER_MOVE_RIGHT, new PlayerMoveRightEvent());
                    break;

                case self::FIRE_KEY:
                    $this->eventDispatcher->dispatch(Events::PLAYER_FIRE, new PlayerFireEvent());
                    break;
            }
        }
    }
}
