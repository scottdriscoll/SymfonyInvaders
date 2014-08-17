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
use SD\InvadersBundle\Event\BossHitEvent;
use SD\InvadersBundle\Event\AlienProjectileEndEvent;
use SD\InvadersBundle\Helpers\OutputHelper;
use SD\Game\Projectile\AbstractProjectile;
use SD\Game\Projectile\PlayerProjectile;
use SD\Game\Projectile\AlienProjectile;
use SD\Game\Projectile\BossProjectile;

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
    public function redrawProjectiles(RedrawEvent $event)
    {
        $output = $event->getOutput();
        $this->drawProjectiles($output, $this->playerProjectiles);
        $this->drawProjectiles($output, $this->alienProjectiles);
    }

    /**
     * @param int $xPosition
     * @param int $yPosition
     * @param int $velocity
     */
    public function firePlayerProjectile($xPosition, $yPosition, $velocity)
    {
        $this->playerProjectiles[] = new PlayerProjectile($xPosition, $yPosition, microtime(true), $velocity);
    }

    /**
     * @param int $xPosition
     * @param int $yPosition
     * @param int $velocity
     */
    public function fireAlienProjectile($xPosition, $yPosition, $velocity)
    {
        $this->alienProjectiles[] = new AlienProjectile($xPosition, $yPosition, microtime(true), $velocity);
    }

    /**
     * @param int $xPosition
     * @param int $yPosition
     * @param int $velocity
     */
    public function fireBossProjectile($xPosition, $yPosition, $velocity)
    {
        $this->alienProjectiles[] = new BossProjectile($xPosition, $yPosition, microtime(true), $velocity);
    }

    /**
     * @return int
     */
    public function getAlienProjectileCount()
    {
        $total = 0;

        foreach ($this->alienProjectiles as $projectile) {
            if (get_class($projectile) == 'SD\Game\Projectile\AlienProjectile') {
                $total++;
            }
        }

        return $total;
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

        /** @var AbstractProjectile $projectile */
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
        $this->updateEnemyProjectiles($this->alienProjectiles, $event->getTimestamp());
    }

    /**
     * @DI\Observe(Events::ALIEN_HIT, priority = 0)
     *
     * @param AlienHitEvent $event
     */
    public function alienHit(AlienHitEvent $event)
    {
        $this->removePlayerProjectile($event->getProjectileIndex());
    }

    /**
     * @DI\Observe(Events::BOSS_HIT, priority = 0)
     *
     * @param BossHitEvent $event
     */
    public function bossHit(BossHitEvent $event)
    {
        $this->removePlayerProjectile($event->getProjectileIndex());
    }

    /**
     * @param int $idx
     */
    private function removePlayerProjectile($idx)
    {
        if (isset($this->playerProjectiles[$idx])) {
            unset($this->playerProjectiles[$idx]);
        }
    }

    /**
     * @param array $projectiles
     * @param int $timestamp
     */
    private function updateEnemyProjectiles(array &$projectiles, $timestamp)
    {
        /** @var AbstractProjectile $projectile */
        foreach ($projectiles as $idx => $projectile) {
            if ($timestamp >= $projectile->getLastUpdatedTime() + $projectile->getVelocity()) {
                $projectile->setYPosition($projectile->getYPosition() + 1);
                $projectile->setLastUpdatedTime($timestamp);
                if ($projectile->getYPosition() == $this->boardHeight - 1) {
                    $this->eventDispatcher->dispatch(Events::ALIEN_PROJECTILE_END, new AlienProjectileEndEvent($projectile->getXPosition()));
                    unset($projectiles[$idx]);
                }
            }
        }
    }

    /**
     * @param ScreenBuffer $output
     * @param array $projectiles
     */
    private function drawProjectiles(ScreenBuffer $output, array $projectiles)
    {
        /** @var AbstractProjectile $projectile */
        foreach ($projectiles as $projectile) {
            $projectile->draw($output);
        }
    }
}
