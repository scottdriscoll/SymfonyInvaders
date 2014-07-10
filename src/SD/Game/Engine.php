<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use SD\InvadersBundle\Event\HeartbeatEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\InvadersBundle\Events;

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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var bool
     */
    private $gameOver = false;

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

    public function run()
    {
        while (!$this->gameOver) {
            $this->eventDispatcher->dispatch(Events::HEARTBEAT, new HeartbeatEvent(microtime(true)));

            usleep(self::HEARTBEAT_DURATION);
        }
    }
}
