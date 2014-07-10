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
    const RIGHT_ARROW = 'C';

    /**
     * @var string
     */
    const LEFT_ARROW = 'D';

    /**
     * @var string
     */
    const FIRE_KEY = ' ';

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @DI\Observe(Events::HEARTBEAT, priority = 0)
     *
     * @param HeartbeatEvent $event
     */
    public function processKeyboardEvents(HeartbeatEvent $event)
    {
        if (($key = $this->nonblockingRead()) !== null) {
            switch ($key) {
                case self::LEFT_ARROW:
                    $this->eventDispatcher->dispatch(Events::PLAYER_MOVE_LEFT, new PlayerMoveLeftEvent());
                    break;

                case self::RIGHT_ARROW:
                    $this->eventDispatcher->dispatch(Events::PLAYER_MOVE_RIGHT, new PlayerMoveRightEvent());
                    break;

                case self::FIRE_KEY:
                    $this->eventDispatcher->dispatch(Events::PLAYER_FIRE, new PlayerFireEvent());
                    break;
            }
        }
    }

    /**
     * Reads from a stream without waiting for a \n character.
     *
     * @return string
     */
    private function nonblockingRead()
    {
        $read = [STDIN];
        $write = [];
        $except = [];
        $result = stream_select($read, $write, $except, 0);

        if ($result === false || $result === 0) {
            return null;
        }

        return stream_get_line(STDIN, 1);
    }
}
