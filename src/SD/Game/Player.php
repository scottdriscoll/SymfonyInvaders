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
use SD\InvadersBundle\Event\PlayerInitializedEvent;

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
    const PROJECTILE_VELOCITY = 500;

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
    private $yPosition;

    /**
     * @var int
     */
    private $maximumXPosition;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var array
     */
    private $activeProjectiles = [];

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
     * @param int $currentXPosition
     * @param int $yPosition
     */
    public function initialize($minimumXPosition, $maximumXPosition, $currentXPosition, $yPosition)
    {
        $this->minimumXPosition = $minimumXPosition;
        $this->maximumXPosition = $maximumXPosition;
        $this->currentXPosition = $currentXPosition;
        $this->yPosition = $yPosition;
        $this->eventDispatcher->dispatch(Events::PLAYER_INITIALIZED, new PlayerInitializedEvent($this->maxHealth, $this->currentXPosition));
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
            $this->eventDispatcher->dispatch(Events::PLAYER_MOVED, new PlayerMovedEvent($this->currentHealth, $this->maxHealth, $this->currentXPosition));
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
            $this->eventDispatcher->dispatch(Events::PLAYER_MOVED, new PlayerMovedEvent($this->currentHealth, $this->maxHealth, $this->currentXPosition));
        }
    }

    /**
     * @DI\Observe(Events::PLAYER_FIRE, priority = 0)
     *
     * @param PlayerFireEvent $event
     */
    public function fire(PlayerFireEvent $event)
    {
        $this->activeProjectiles[] = new Projectile($this->currentXPosition, $this->yPosition, microtime(true), self::PROJECTILE_VELOCITY);
    }
}
