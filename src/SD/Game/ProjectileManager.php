<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\InvadersBundle\Events;
use SD\InvadersBundle\Event\HeartbeatEvent;
use SD\InvadersBundle\Event\RedrawEvent;
use SD\InvadersBundle\Event\PlayerProjectilesUpdatedEvent;
use SD\InvadersBundle\Event\AlienHitEvent;
use SD\InvadersBundle\Event\AlienProjectileEndEvent;

/**
 * @DI\Service("game.projectile.manager")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class ProjectileManager
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var array
     */
    private $playerProjectiles = [];

    /**
     * @var array
     */
    private $alienProjectiles = [];

    /**
     * @var int
     */
    private $boardHeight;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "boardHeight" = @DI\Inject("%board_height%")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param int $boardHeight
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $boardHeight)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->boardHeight = $boardHeight;
    }

    /**
     * @DI\Observe(Events::BOARD_REDRAW, priority = -2)
     *
     * @param RedrawEvent $event
     */
    public function redrawPlayerProjectiles(RedrawEvent $event)
    {
        $output = $event->getOutput();

        /** @var Projectile $projectile */
        foreach ($this->playerProjectiles as $projectile) {
            $output->moveCursorDown($this->boardHeight);
            $output->moveCursorFullLeft();
            $output->moveCursorUp($this->boardHeight - $projectile->getYPosition());
            $output->moveCursorRight($projectile->getXPosition());
            $output->write('<fg=red>|</fg=red>');
        }

        $output->moveCursorDown($this->boardHeight);
        $output->moveCursorFullLeft();
    }

    /**
     * @DI\Observe(Events::BOARD_REDRAW, priority = -2)
     *
     * @param RedrawEvent $event
     */
    public function redrawAlienProjectiles(RedrawEvent $event)
    {
        $output = $event->getOutput();

        /** @var Projectile $projectile */
        foreach ($this->alienProjectiles as $projectile) {
            $output->moveCursorDown($this->boardHeight);
            $output->moveCursorFullLeft();
            $output->moveCursorUp($this->boardHeight - $projectile->getYPosition() - 1);
            $output->moveCursorRight($projectile->getXPosition());
            $output->write('<fg=red>|</fg=red>');
        }
    }

    /**
     * @param int $xPosition
     * @param int $yPosition
     * @param int $velocity
     */
    public function firePlayerProjectile($xPosition, $yPosition, $velocity)
    {
        $this->playerProjectiles[] = new Projectile($xPosition, $yPosition, microtime(true), $velocity);
    }

    /**
     * @param int $xPosition
     * @param int $yPosition
     * @param int $velocity
     */
    public function fireAlienProjectile($xPosition, $yPosition, $velocity)
    {
        $this->alienProjectiles[] = new Projectile($xPosition, $yPosition, microtime(true), $velocity);
    }

    /**
     * @return int
     */
    public function getAlienProjectileCount()
    {
        return count($this->alienProjectiles);
    }

    /**
     * @DI\Observe(Events::HEARTBEAT, priority = -2)
     *
     * @param HeartbeatEvent $event
     */
    public function updatePlayerProjectiles(HeartbeatEvent $event)
    {
        $updated = false;
        $currentTime = $event->getTimestamp();

        /** @var Projectile $projectile */
        foreach ($this->playerProjectiles as $idx => $projectile) {
            if ($currentTime >= $projectile->getLastUpdatedTime() + $projectile->getVelocity()) {
                $projectile->setLastUpdatedTime($currentTime);
                $updated = true;

                $projectile->setYPosition($projectile->getYPosition() - 1);
                if ($projectile->getYPosition() <= 0) {
                    unset($this->playerProjectiles[$idx]);
                }
            }
        }

        if ($updated) {
            $this->eventDispatcher->dispatch(Events::PLAYER_PROJECTILES_UPDATED, new PlayerProjectilesUpdatedEvent($this->playerProjectiles));
        }
    }

    /**
     * @DI\Observe(Events::HEARTBEAT, priority = -1)
     *
     * @param HeartbeatEvent $event
     */
    public function updateAlienProjectiles(HeartbeatEvent $event)
    {
        /** @var Projectile $projectile */
        foreach ($this->alienProjectiles as $idx => $projectile) {
            if ($event->getTimestamp() >= $projectile->getLastUpdatedTime() + $projectile->getVelocity()) {
                $projectile->setYPosition($projectile->getYPosition() + 1);
                $projectile->setLastUpdatedTime($event->getTimestamp());
                if ($projectile->getYPosition() == $this->boardHeight - 2) {
                    $this->eventDispatcher->dispatch(Events::ALIEN_PROJECTILE_END, new AlienProjectileEndEvent($projectile->getXPosition()));
                    unset($this->alienProjectiles[$idx]);
                }
            }
        }
    }

    /**
     * @DI\Observe(Events::ALIEN_HIT, priority = 0)
     *
     * @param AlienHitEvent $event
     */
    public function alienHit(AlienHitEvent $event)
    {
        $idx = $event->getProjectileIndex();

        if (isset($this->playerProjectiles[$idx])) {
            unset($this->playerProjectiles[$idx]);
        }
    }
}
