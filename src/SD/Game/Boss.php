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
use SD\InvadersBundle\Event\BossHitEvent;
use SD\InvadersBundle\Event\BossDeadEvent;
use SD\InvadersBundle\Event\PlayerProjectilesUpdatedEvent;

/**
 * @DI\Service("game.boss")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class Boss
{
    const MAX_HEALTH = 100;

    const BOSS_WIDTH = 5;

    const BOSS_HEIGHT = 3;

    const DIRECTION_LEFT = 1;

    const DIRECTION_RIGHT = 2;

    const BOSS_VELOCITY_DEFAULT = 0.075;

    const FIRE_DELAY = 0.75;

    const PROJECTILE_VELOCITY = 0.075;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ProjectileManager
     */
    private $projectileManager;

    /**
     * @var AlienManager
     */
    private $alienManager;

    /**
     * @var bool
     */
    private $spawned = false;

    /**
     * @var int
     */
    private $xPosition;

    /**
     * @var int
     */
    private $boardHeight;

    /**
     * @var int
     */
    private $boardWidth;

    /**
     * @var int
     */
    private $currentHealth = self::MAX_HEALTH;

    /**
     * @var int
     */
    private $currentDirection = self::DIRECTION_LEFT;

    /**
     * @var int
     */
    private $lastMoveUpdate = 0;

    /**
     * @var int
     */
    private $lastFireUpdate = 0;

    /**
     * @var double
     */
    private $projectileVelocityModifier = 0;

    /**
     * @var double
     */
    private $bossVelocityModifier = 0;

    /**
     * @var double
     */
    private $bossFireDelayModifier = 0;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "projectileManager" = @DI\Inject("game.projectile.manager"),
     *     "alienManager" = @DI\Inject("game.alien.manager")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param ProjectileManager $projectileManager
     * @param AlienManager $alienManager
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ProjectileManager $projectileManager, AlienManager $alienManager)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->projectileManager = $projectileManager;
        $this->alienManager = $alienManager;
    }

    /**
     * @param int $width
     * @param int $height
     */
    public function spawnBoss($width, $height)
    {
        $this->boardWidth = $width;
        $this->boardHeight = $height;
        $this->xPosition = (int) ($width / 2 - (int) self::BOSS_WIDTH / 2);
        $this->spawned = true;
    }

    /**
     * @return bool
     */
    public function isSpawned()
    {
        return $this->spawned;
    }

    /**
     * @return int
     */
    public function getCurrentHealth()
    {
        return $this->currentHealth;
    }

    /**
     * @DI\Observe(Events::HEARTBEAT, priority = -2)
     *
     * @param HeartbeatEvent $event
     */
    public function updateBoss(HeartbeatEvent $event)
    {
        if (!$this->spawned) {
            return;
        }

        $currentTime = $event->getTimestamp();

        // Test if he wants to move
        if ($currentTime >= self::BOSS_VELOCITY_DEFAULT + $this->lastMoveUpdate - $this->bossVelocityModifier) {
            $this->lastMoveUpdate = $currentTime;
            if ($this->currentDirection == self::DIRECTION_LEFT) {
                $this->xPosition--;

                if ($this->xPosition <= 1) {
                    $this->xPosition++;
                    $this->currentDirection = self::DIRECTION_RIGHT;
                }
            } else {
                $this->xPosition++;

                if ($this->xPosition + self::BOSS_WIDTH >= $this->boardWidth) {
                    $this->currentDirection = self::DIRECTION_LEFT;
                }
            }
        }

        // Test if he wants to shoot
        if ($currentTime >= self::FIRE_DELAY + $this->lastFireUpdate - $this->bossFireDelayModifier) {
            $this->lastFireUpdate = $currentTime;

            for ($x = $this->xPosition; $x < $this->xPosition + self::BOSS_WIDTH; $x++) {
                $this->projectileManager->fireBossProjectile($x, self::BOSS_HEIGHT + 2, self::PROJECTILE_VELOCITY - $this->projectileVelocityModifier);
            }
        }
    }

    /**
     * @DI\Observe(Events::BOARD_REDRAW, priority = -2)
     *
     * @param RedrawEvent $event
     */
    public function redrawBoss(RedrawEvent $event)
    {
        if (!$this->spawned) {
            return;
        }

        $output = $event->getOutput();

        $output->moveCursorDown($this->boardHeight);
        $output->moveCursorFullLeft();
        $output->moveCursorUp($this->boardHeight - 2);
        $output->moveCursorRight($this->xPosition);

        $top = '<fg=yellow>' . str_pad('', self::BOSS_WIDTH, '^') . '</fg=yellow>';
        $output->writeln($top);

        $middle = '<fg=yellow><' . str_pad('', self::BOSS_WIDTH - 2, 'o') . '></fg=yellow>';
        for ($i = 0; $i < self::BOSS_HEIGHT - 2; $i++) {
            $output->moveCursorFullLeft();
            $output->moveCursorRight($this->xPosition);
            $output->writeln($middle);
        }

        $bottom = '<fg=yellow>' . str_pad('', self::BOSS_WIDTH, 'v') . '</fg=yellow>';
        $output->moveCursorFullLeft();
        $output->moveCursorRight($this->xPosition);
        $output->writeln($bottom);
    }

    /**
     * @DI\Observe(Events::PLAYER_PROJECTILES_UPDATED, priority = 0)
     *
     * @param PlayerProjectilesUpdatedEvent $event
     */
    public function testForCollision(PlayerProjectilesUpdatedEvent $event)
    {
        if (!$this->spawned || $this->currentHealth <= 0) {
            return;
        }

        $hit = false;
        /** @var Projectile $projectile */
        foreach ($event->getProjectiles() as $idx => $projectile) {
            if ($projectile->getXPosition() >= $this->xPosition && $projectile->getXPosition() <= $this->xPosition + self::BOSS_WIDTH &&
                $projectile->getYPosition() <= 2 && $this->currentHealth > 0) {
                $this->currentHealth--;
                $hit = true;
                $this->eventDispatcher->dispatch(Events::BOSS_HIT, new BossHitEvent($this->currentHealth, $idx));
                break;
            }
        }

        if ($hit) {
            if ($this->currentHealth == 0) {
                $this->eventDispatcher->dispatch(Events::BOSS_DEAD, new BossDeadEvent());
            } elseif ($this->currentHealth < self::MAX_HEALTH && $this->currentHealth % 13 == 0) {
                $this->projectileVelocityModifier += (self::PROJECTILE_VELOCITY * 0.01);
            } elseif ($this->currentHealth % 2 == 0) {
                $animationFrames = ['o', 'O'];
                // Spawn some mobs
                for ($x = $this->xPosition; $x < $this->xPosition + self::BOSS_WIDTH; $x++) {
                    $this->alienManager->spawnMob($x, self::BOSS_HEIGHT + 1, AlienManager::DEFAULT_FIRE_CHANCE_DEFAULT, AlienManager::FIRE_DELAY, self::BOSS_VELOCITY_DEFAULT / 2, $animationFrames);
                    $this->alienManager->spawnMob($x, self::BOSS_HEIGHT + 2, AlienManager::DEFAULT_FIRE_CHANCE_DEFAULT, AlienManager::FIRE_DELAY, self::BOSS_VELOCITY_DEFAULT / 2, $animationFrames);
                    $this->alienManager->spawnMob($x, self::BOSS_HEIGHT + 3, AlienManager::DEFAULT_FIRE_CHANCE_DEFAULT, AlienManager::FIRE_DELAY, self::BOSS_VELOCITY_DEFAULT / 2, $animationFrames);
                }
            }
        }
    }
}
