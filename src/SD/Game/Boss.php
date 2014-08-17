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
use SD\InvadersBundle\Event\BossDyingEvent;
use SD\InvadersBundle\Event\PlayerProjectilesUpdatedEvent;
use SD\InvadersBundle\Event\AlienProjectileEndEvent;
use SD\Game\Projectile\AbstractProjectile;

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

    const FIRE_DELAY = 0.15;

    const PROJECTILE_VELOCITY = 0.175;

    const STATE_WAITING = 0;

    const STATE_ALIVE = 1;

    const STATE_DYING = 2;

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
     * @var int
     */
    private $currentState = self::STATE_WAITING;

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
     * @var int
     */
    private $lastFirePosition = 1;

    /**
     * @var double
     */
    private $projectileVelocityModifier = 0;

    /**
     * @var array
     */
    private $bossArray = [
        '^^^^^',
        '<ooo>',
        'vvvvv'
    ];

    /**
     * @var array
     */
    private $bossDyingArray = [];

    /**
     * @var array
     */
    private $fireCoordinates = [
        1 => [1, 1],
        2 => [2, 1],
        3 => [3, 1],
        4 => [4, 1],
        5 => [5, 1],
        6 => [5, 2],
        7 => [5, 3],
        8 => [4, 3],
        9 => [3, 3],
        10 => [2, 3],
        11 => [1, 3],
        12 => [1, 2]
    ];

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
        $this->currentState = self::STATE_ALIVE;
    }

    /**
     * @return bool
     */
    public function isAlive()
    {
        return $this->currentState == self::STATE_ALIVE;
    }

    /**
     * @return int
     */
    public function getState()
    {
        return $this->currentState;
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
        $currentTime = $event->getTimestamp();

        if ($this->currentState == self::STATE_DYING) {
            $this->updateDyingBoss($currentTime);

            return;
        }

        if ($this->currentState != self::STATE_ALIVE) {
            return;
        }

        // Test if he wants to move
        if ($currentTime >= self::BOSS_VELOCITY_DEFAULT + $this->lastMoveUpdate) {
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
        if ($currentTime >= self::FIRE_DELAY + $this->lastFireUpdate) {
            $this->lastFireUpdate = $currentTime;
            $this->lastFirePosition++;
            if ($this->lastFirePosition > (self::BOSS_WIDTH * 2 + (self::BOSS_HEIGHT - 2) * 2)) {
                $this->lastFirePosition = 1;
            }

            if ($this->lastFirePosition >= (self::BOSS_WIDTH + self::BOSS_HEIGHT - 2) && $this->lastFirePosition <= (self::BOSS_WIDTH * 2 + self::BOSS_HEIGHT - 0)) {
                $coordinates = $this->fireCoordinates[$this->lastFirePosition];
                $this->projectileManager->fireBossProjectile($this->xPosition + $coordinates[0] - 1, self::BOSS_HEIGHT + 2, self::PROJECTILE_VELOCITY - $this->projectileVelocityModifier);
            }

            if ($this->lastFirePosition % 4 == 0) {
                for ($x = $this->xPosition; $x < $this->xPosition + self::BOSS_WIDTH; $x++) {
                    $this->projectileManager->fireBossProjectile($x, self::BOSS_HEIGHT + 2, self::PROJECTILE_VELOCITY / 2 - $this->projectileVelocityModifier);
                }
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
        $output = $event->getOutput();

        if ($this->currentState == self::STATE_DYING) {
            foreach ($this->bossDyingArray as $bossPiece) {
                $output->putNextValue($bossPiece['x'], $bossPiece['y'], $bossPiece['character'], $bossPiece['color']);
            }

            return;
        }

        if ($this->currentState != self::STATE_ALIVE) {
            return;
        }

        $output->putArrayOfValues($this->xPosition, 1, $this->bossArray, 'yellow');
        $coordinates = $this->fireCoordinates[$this->lastFirePosition];
        if ($this->lastFirePosition <= self::BOSS_WIDTH) {
            $character = '^';
        } elseif ($this->lastFirePosition > self::BOSS_WIDTH && $this->lastFirePosition < self::BOSS_WIDTH + self::BOSS_HEIGHT - 1) {
            $character = '>';
        } elseif ($this->lastFirePosition >= (self::BOSS_WIDTH + self::BOSS_HEIGHT - 2) && $this->lastFirePosition <= (self::BOSS_WIDTH * 2 + self::BOSS_HEIGHT - 2)) {
            $character = 'v';
        } else {
            $character = '<';
        }

        $output->putNextValue($this->xPosition + $coordinates[0] - 1, $coordinates[1], $character, 'red');
    }

    /**
     * @DI\Observe(Events::PLAYER_PROJECTILES_UPDATED, priority = 0)
     *
     * @param PlayerProjectilesUpdatedEvent $event
     */
    public function testForCollision(PlayerProjectilesUpdatedEvent $event)
    {
        if ($this->currentState != self::STATE_ALIVE) {
            return;
        }

        $hit = false;
        /** @var AbstractProjectile $projectile */
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
                $this->killBoss();
            } elseif ($this->currentHealth < self::MAX_HEALTH && $this->currentHealth % 13 == 0) {
                $this->projectileVelocityModifier += (self::PROJECTILE_VELOCITY * 0.01);
            } elseif ($this->currentHealth % 2 == 0) {
                $animationFrames = ['o', 'O'];
                // Spawn some mobs
                for ($x = $this->xPosition; $x < $this->xPosition + self::BOSS_WIDTH; $x++) {
                    $this->alienManager->spawnMob($x, self::BOSS_HEIGHT + 1, AlienManager::DEFAULT_FIRE_CHANCE_DEFAULT * 2, AlienManager::FIRE_DELAY, self::BOSS_VELOCITY_DEFAULT / 2, $animationFrames);
                    $this->alienManager->spawnMob($x, self::BOSS_HEIGHT + 2, AlienManager::DEFAULT_FIRE_CHANCE_DEFAULT * 2, AlienManager::FIRE_DELAY, self::BOSS_VELOCITY_DEFAULT / 2, $animationFrames);
                    $this->alienManager->spawnMob($x, self::BOSS_HEIGHT + 3, AlienManager::DEFAULT_FIRE_CHANCE_DEFAULT * 2, AlienManager::FIRE_DELAY, self::BOSS_VELOCITY_DEFAULT / 2, $animationFrames);
                }
            }
        }
    }

    /**
     * Sets up dying animation
     */
    private function killBoss()
    {
        $this->currentState = self::STATE_DYING;

        $y = 1;
        foreach ($this->bossArray as $bossPiece) {
            $len = strlen($bossPiece);
            for ($i = 0; $i < $len; $i++) {
                $this->bossDyingArray[] = [
                    'character' => substr($bossPiece, $i, 1),
                    'x' => $this->xPosition + $i,
                    'y' => $y,
                    'color' => rand(0, 1) == 0 ? 'red' : 'yellow',
                    'velocity' => self::BOSS_VELOCITY_DEFAULT / rand(1, 2),
                    'last_update' => 0,
                    'direction' => rand(self::DIRECTION_LEFT, self::DIRECTION_RIGHT)
                ];
            }

            $y++;
        }

        $this->eventDispatcher->dispatch(Events::BOSS_DYING, new BossDyingEvent());
    }

    /**
     * @param int $currentTime
     */
    private function updateDyingBoss($currentTime)
    {
        foreach ($this->bossDyingArray as $idx => $bossPiece) {
            if ($currentTime >= $bossPiece['velocity'] + $bossPiece['last_update']) {
                $this->bossDyingArray[$idx]['last_update'] = $currentTime;
                $this->bossDyingArray[$idx]['y']++;

                if ($this->bossDyingArray[$idx]['y'] == $this->boardHeight - 1) {
                    $this->eventDispatcher->dispatch(Events::ALIEN_PROJECTILE_END, new AlienProjectileEndEvent($this->bossDyingArray[$idx]['x']));
                    unset($this->bossDyingArray[$idx]);
                } elseif ($bossPiece['direction'] == self::DIRECTION_LEFT) {
                    $this->bossDyingArray[$idx]['x']--;
                    if ($this->bossDyingArray[$idx]['x'] <= 1) {
                        $this->bossDyingArray[$idx]['direction'] = self::DIRECTION_RIGHT;
                    }
                } else {
                    $this->bossDyingArray[$idx]['x']++;
                    if ($this->bossDyingArray[$idx]['x'] >= $this->boardWidth) {
                        $this->bossDyingArray[$idx]['direction'] = self::DIRECTION_LEFT;
                    }
                }
            }
        }

        if (!count($this->bossDyingArray) && !$this->alienManager->getAlienCount() && !$this->projectileManager->getEnemyProjectileCount()) {
            $this->eventDispatcher->dispatch(Events::BOSS_DEAD, new BossDeadEvent());
        }
    }
}
