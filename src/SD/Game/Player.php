<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use JMS\DiExtraBundle\Annotation as DI;
use SD\InvadersBundle\Event\PlayerProjectilesUpdatedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use SD\InvadersBundle\Events;
use SD\InvadersBundle\Event\PlayerMoveLeftEvent;
use SD\InvadersBundle\Event\PlayerMoveRightEvent;
use SD\InvadersBundle\Event\PlayerFireEvent;
use SD\InvadersBundle\Event\PlayerMovedEvent;
use SD\InvadersBundle\Event\PlayerInitializedEvent;
use SD\InvadersBundle\Event\HeartbeatEvent;
use SD\InvadersBundle\Event\RedrawEvent;
use SD\InvadersBundle\Event\AlienProjectileEndEvent;
use SD\InvadersBundle\Event\AlienHitEvent;
use SD\InvadersBundle\Event\PlayerHitEvent;

/**
 * @DI\Service("game.player")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class Player
{
    /**
     * @var double
     */
    const PROJECTILE_VELOCITY = 0.025;

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
    private $yPosition;

    /**
     * @var int
     */
    private $minimumXPosition;

    /**
     * @var int
     */
    private $maximumXPosition;

    /**
     * @var ProjectileManager
     */
    private $projectileManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

   /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "projectileManager" = @DI\Inject("game.projectile.manager"),
     *     "boardWidth" = @DI\Inject("%board_width%"),
     *     "boardHeight" = @DI\Inject("%board_height%")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param ProjectileManager $projectileManager
     * @param int $boardWidth
     * @param int $boardHeight
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ProjectileManager $projectileManager, $boardWidth, $boardHeight)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->projectileManager = $projectileManager;
        $this->minimumXPosition = 0;
        $this->maximumXPosition = $boardWidth - 3;
        $this->currentXPosition = (int) $boardWidth / 2;
        $this->yPosition = $boardHeight - 2;
    }

    public function initialize()
    {
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
        $this->projectileManager->firePlayerProjectile($this->currentXPosition + 1, $this->yPosition - 1, self::PROJECTILE_VELOCITY);
    }

    /**
     * @DI\Observe(Events::ALIEN_PROJECTILE_END, priority = 0)
     *
     * @param AlienProjectileEndEvent $event
     */
    public function alienProjectileReachedEnd(AlienProjectileEndEvent $event)
    {
        if ($event->getXPosition() == $this->currentXPosition + 1) {
            $this->eventDispatcher->dispatch(Events::PLAYER_HIT, new PlayerHitEvent());
        } else {
            $this->eventDispatcher->dispatch(Events::PLAYER_MOVED, new PlayerMovedEvent($this->currentHealth, $this->maxHealth, $this->currentXPosition));
        }
    }
}
