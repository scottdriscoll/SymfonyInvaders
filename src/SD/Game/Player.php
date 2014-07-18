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
    private $projectileYMaximum;

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
     * @var array
     */
    private $activeProjectiles = [];

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "boardWidth" = @DI\Inject("%board_width%"),
     *     "boardHeight" = @DI\Inject("%board_height%")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param int $boardWidth
     * @param int $boardHeight
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $boardWidth, $boardHeight)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->minimumXPosition = 0;
        $this->maximumXPosition = $boardWidth - 3;
        $this->currentXPosition = (int) $boardWidth / 2;
        $this->yPosition = $boardHeight - 2;
        $this->projectileYMaximum = $boardHeight;
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
        $this->activeProjectiles[] = new Projectile($this->currentXPosition, $this->yPosition - 1, microtime(true), self::PROJECTILE_VELOCITY);
    }

    /**
     * @DI\Observe(Events::HEARTBEAT, priority = 0)
     *
     * @param HeartbeatEvent $event
     */
    public function updateProjectiles(HeartbeatEvent $event)
    {
        $updated = false;
        $currentTime = $event->getTimestamp();

        /** @var Projectile $projectile */
        foreach ($this->activeProjectiles as $idx => $projectile) {
            if ($currentTime >= $projectile->getLastUpdatedTime() + $projectile->getVelocity()) {
                $projectile->setLastUpdatedTime($currentTime);
                $updated = true;

                $projectile->setYPosition($projectile->getYPosition() - 1);
                if ($projectile->getYPosition() <= 0) {
                    unset($this->activeProjectiles[$idx]);
                }
            }
        }

        if ($updated) {
            $this->eventDispatcher->dispatch(Events::PLAYER_PROJECTILES_UPDATED, new PlayerProjectilesUpdatedEvent($this->activeProjectiles));
        }
    }

    /**
     * @DI\Observe(Events::BOARD_REDRAW, priority = 0)
     *
     * @param RedrawEvent $event
     */
    public function redrawProjectiles(RedrawEvent $event)
    {
        $output = $event->getOutput();

        /** @var Projectile $projectile */
        foreach ($this->activeProjectiles as $projectile) {
            $output->moveCursorDown($this->projectileYMaximum);
            $output->moveCursorFullLeft();
            $output->moveCursorUp($this->projectileYMaximum - $projectile->getYPosition());
            $output->moveCursorRight($projectile->getXPosition());
            $output->write('<fg=red>|</fg=red>');
        }

        $output->moveCursorDown($this->projectileYMaximum);
        $output->moveCursorFullLeft();
    }

    /**
     * @DI\Observe(Events::ALIEN_PROJECTILE_END, priority = 0)
     *
     * @param AlienProjectileEndEvent $event
     */
    public function alienProjectileReachedEnd(AlienProjectileEndEvent $event)
    {

        $this->eventDispatcher->dispatch(Events::PLAYER_MOVED, new PlayerMovedEvent($this->currentHealth, $this->maxHealth, $this->currentXPosition));
    }

    /**
     * @DI\Observe(Events::ALIEN_HIT, priority = 0)
     *
     * @param AlienHitEvent $event
     */
    public function alienHit(AlienHitEvent $event)
    {
        $idx = $event->getProjectileIndex();

        if (isset($this->activeProjectiles[$idx])) {
            unset($this->activeProjectiles[$idx]);
        }
    }
}
