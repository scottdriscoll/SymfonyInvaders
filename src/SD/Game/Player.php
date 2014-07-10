<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use SD\InvadersBundle\Events;
use SD\InvadersBundle\Event\PlayerMoveLeftEvent;
use SD\InvadersBundle\Event\PlayerMoveRightEvent;
use SD\InvadersBundle\Event\PlayerFireEvent;
use SD\InvadersBundle\Event\PlayerMovedEvent;

/**
 * @DI\Service("game.player")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class Player
{
    /**
     * @var int
     */
    private $maxHealth = 5;

    /**
     * @var int
     */
    private $currentHealth = 5;

    /**
     * @var int
     */
    private $currentXPosition;

    /**
     * @var int
     */
    private $minimumXPosition;

    /**
     * @var int
     */
    private $maximumXPosition;

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
     * @param int $minimumXPosition
     * @param int $maximumXPosition
     */
    public function initialize($minimumXPosition, $maximumXPosition)
    {
        $this->minimumXPosition = $minimumXPosition;
        $this->maximumXPosition = $maximumXPosition;
    }

    /**
     * @DI\Observe(Events::PLAYER_MOVE_LEFT, priority = 0)
     *
     * @param PlayerMoveLeftEvent $event
     */
    public function moveLeft(PlayerMoveLeftEvent $event)
    {
        if ($this->currentXPosition > $this->minimumXPosition) {
            $this->currentXPosition--;
            $this->eventDispatcher->dispatch(Events::PLAYER_MOVED, new PlayerMovedEvent($this->currentHealth, $this->maxHealth));
        }
    }

    /**
     * @DI\Observe(Events::PLAYER_MOVE_RIGHT, priority = 0)
     *
     * @param PlayerMoveRightEvent $event
     */
    public function moveRight(PlayerMoveRightEvent $event)
    {
        if ($this->currentXPosition < $this->maximumXPosition) {
            $this->currentXPosition++;
            $this->eventDispatcher->dispatch(Events::PLAYER_MOVED, new PlayerMovedEvent($this->currentHealth, $this->maxHealth));
        }
    }

    /**
     * @DI\Observe(Events::PLAYER_FIRE, priority = 0)
     *
     * @param PlayerFireEvent $event
     */
    public function fire(PlayerFireEvent $event)
    {

    }
}
