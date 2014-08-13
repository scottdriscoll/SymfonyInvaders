<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\InvadersBundle\Events;
use SD\InvadersBundle\Event\HeartbeatEvent;
use SD\InvadersBundle\Event\GameOverEvent;

/**
 * @DI\Service("game.engine")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class Engine
{
    
    const FRAMES_PER_SEC = 60;
    
    const ONE_SEC_MICRO = 1000000;

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
            $time_start = microtime(true);
            $this->eventDispatcher->dispatch(Events::HEARTBEAT, new HeartbeatEvent(microtime(true)));
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            
            usleep((self::ONE_SEC_MICRO / self::FRAMES_PER_SEC) - $time);
        }
    }

    /**
     * @DI\Observe(Events::GAME_OVER, priority = 0)
     */
    public function gameOver()
    {
        $this->gameOver = true;
    }
}
