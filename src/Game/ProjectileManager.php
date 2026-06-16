<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Game;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Event\HeartbeatEvent;
use App\Event\RedrawEvent;
use App\Event\PlayerProjectilesUpdatedEvent;
use App\Event\AlienHitEvent;
use App\Event\BossHitEvent;
use App\Event\AlienProjectileEndEvent;
use App\Game\Projectile\AbstractProjectile;
use App\Game\Projectile\PlayerProjectile;
use App\Game\Projectile\AlienProjectile;
use App\Game\Projectile\BossProjectile;
use App\Tui\GameFrame;

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

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        private readonly GameClock $gameClock,
        #[Autowire('%board_height%')]
        $boardHeight,
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->boardHeight = $boardHeight;
    }

    #[AsEventListener(priority: -2)]
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
        $this->playerProjectiles[] = new PlayerProjectile($xPosition, $yPosition, $this->gameClock->now(), $velocity);
    }

    /**
     * @param int $xPosition
     * @param int $yPosition
     * @param int $velocity
     */
    public function fireAlienProjectile($xPosition, $yPosition, $velocity)
    {
        $this->alienProjectiles[] = new AlienProjectile($xPosition, $yPosition, $this->gameClock->now(), $velocity);
    }

    /**
     * @param int $xPosition
     * @param int $yPosition
     * @param int $velocity
     */
    public function fireBossProjectile($xPosition, $yPosition, $velocity)
    {
        $this->alienProjectiles[] = new BossProjectile($xPosition, $yPosition, $this->gameClock->now(), $velocity);
    }

    /**
     * @return int
     */
    public function getAlienProjectileCount()
    {
        $total = 0;

        foreach ($this->alienProjectiles as $projectile) {
            if ($projectile instanceof AlienProjectile) {
                $total++;
            }
        }

        return $total;
    }

    /**
     * @return int
     */
    public function getEnemyProjectileCount()
    {
        return count($this->alienProjectiles);
    }

    #[AsEventListener(priority: -2)]
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
            $this->eventDispatcher->dispatch(new PlayerProjectilesUpdatedEvent($this->playerProjectiles));
        }
    }

    #[AsEventListener(priority: -1)]
    public function updateAlienProjectiles(HeartbeatEvent $event)
    {
        $this->updateEnemyProjectiles($this->alienProjectiles, $event->getTimestamp());
    }

    #[AsEventListener]
    public function alienHit(AlienHitEvent $event)
    {
        $this->removePlayerProjectile($event->getProjectileIndex());
    }

    #[AsEventListener]
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
                    $this->eventDispatcher->dispatch(new AlienProjectileEndEvent($projectile->getXPosition()));
                    unset($projectiles[$idx]);
                }
            }
        }
    }

    /**
     * @param GameFrame $output
     * @param array $projectiles
     */
    private function drawProjectiles(GameFrame $output, array $projectiles)
    {
        /** @var AbstractProjectile $projectile */
        foreach ($projectiles as $projectile) {
            $projectile->draw($output);
        }
    }
}
