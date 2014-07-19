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
use SD\InvadersBundle\Event\AliensUpdatedEvent;
use SD\InvadersBundle\Helpers\OutputHelper;

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
     * @var array
     */
    private $bossProjectiles = [];

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
        $this->drawProjectiles($output, $this->playerProjectiles, 'red');
        $this->drawProjectiles($output, $this->alienProjectiles, 'green');
        $this->drawProjectiles($output, $this->bossProjectiles, 'yellow');
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
     * @param int $xPosition
     * @param int $yPosition
     * @param int $velocity
     */
    public function fireBossProjectile($xPosition, $yPosition, $velocity)
    {
        $this->bossProjectiles[] = new Projectile($xPosition, $yPosition, microtime(true), $velocity);
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
        $this->updateEnemyProjectiles($this->alienProjectiles, $event->getTimestamp());
        $this->updateEnemyProjectiles($this->bossProjectiles, $event->getTimestamp());
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
        $updated = false;

        /** @var Projectile $projectile */
        foreach ($projectiles as $idx => $projectile) {
            if ($timestamp >= $projectile->getLastUpdatedTime() + $projectile->getVelocity()) {
                $projectile->setYPosition($projectile->getYPosition() + 1);
                $updated = true;
                $projectile->setLastUpdatedTime($timestamp);
                if ($projectile->getYPosition() == $this->boardHeight - 1) {
                    $this->eventDispatcher->dispatch(Events::ALIEN_PROJECTILE_END, new AlienProjectileEndEvent($projectile->getXPosition()));
                    unset($projectiles[$idx]);
                }
            }
        }

        if ($updated) {
            $this->eventDispatcher->dispatch(Events::ALIENS_UPDATED, new AliensUpdatedEvent());
        }
    }

    /**
     * @param OutputHelper $output
     * @param array $projectiles
     * @param string $color
     */
    private function drawProjectiles(OutputHelper $output, array $projectiles, $color)
    {
        /** @var Projectile $projectile */
        foreach ($projectiles as $projectile) {
            $output->moveCursorDown($this->boardHeight);
            $output->moveCursorFullLeft();
            $output->moveCursorUp($this->boardHeight - $projectile->getYPosition());
            $output->moveCursorRight($projectile->getXPosition());
            $output->write(sprintf('<fg=%s>|</fg=%s>', $color, $color));
        }
    }
}
