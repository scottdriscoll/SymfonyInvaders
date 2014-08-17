<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use SD\InvadersBundle\Event\AlienReachedEndEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\InvadersBundle\Events;
use SD\InvadersBundle\Event\HeartbeatEvent;
use SD\InvadersBundle\Event\RedrawEvent;
use SD\InvadersBundle\Event\PlayerProjectilesUpdatedEvent;
use SD\InvadersBundle\Event\AlienHitEvent;
use SD\InvadersBundle\Event\AlienDeadEvent;
use SD\InvadersBundle\Event\BossDyingEvent;
use SD\Game\Projectile\AbstractProjectile;

/**
 * @DI\Service("game.alien.manager")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class AlienManager
{
    /**
     * @var double
     */
    const ALIEN_VELOCITY_DEFAULT = 0.075;

    /**
     * @var double
     */
    const PROJECTILE_VELOCITY = .075;

    /**
     * Percent chance per heartbeat that the aliens will fire
     *
     * @var int
     */
    const DEFAULT_FIRE_CHANCE_DEFAULT = 10;

    /**
     * @var double
     */
    const FIRE_DELAY = 1.0;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ProjectileManager
     */
    private $projectileManager;

    /**
     * @var array
     */
    private $aliens = [];

    /**
     * @var int
     */
    private $aliveAliens;

    /**
     * @var int
     */
    private $globalAlienState = Alien::STATE_ALIVE;

    /**
     * @var int
     */
    private $boardWidth;

    /**
     * @var int
     */
    private $boardHeight;

    /**
     * @var
     */
    private $numAlienColumns;

    /**
     * @var
     */
    private $numAlienRows;

    /**
     * @var int
     */
    private $maxProjectiles;

    /**
     * @var int
     */
    private $initialAlienCount;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "projectileManager" = @DI\Inject("game.projectile.manager"),
     *     "boardWidth" = @DI\Inject("%board_width%"),
     *     "boardHeight" = @DI\Inject("%board_height%"),
     *     "numAlienColumns" = @DI\Inject("%alien_columns%"),
     *     "numAlienRows" = @DI\Inject("%alien_rows%"),
     *     "maxProjectiles" = @DI\Inject("%max_alien_projectiles%")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param ProjectileManager $projectileManager
     * @param int $boardWidth
     * @param int $boardHeight
     * @param int $numAlienColumns
     * @param int $numAlienRows
     * @param int $maxProjectiles
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ProjectileManager $projectileManager, $boardWidth, $boardHeight, $numAlienColumns, $numAlienRows, $maxProjectiles)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->projectileManager = $projectileManager;
        $this->boardHeight = $boardHeight;
        $this->boardWidth = $boardWidth;
        $this->numAlienColumns = $numAlienColumns;
        $this->numAlienRows = $numAlienRows;
        $this->maxProjectiles = $maxProjectiles;
    }

    public function initialize()
    {

        for ($i = 0; $i < $this->numAlienRows; $i++) {
            for ($j = 1; $j <= $this->numAlienColumns * 2; $j += 2) {
                $animationFrames = $i % 2 == 0 ? ['[', ']'] : ['}', '{'];
                $this->aliens[] = new Alien($j, $i + 2, self::DEFAULT_FIRE_CHANCE_DEFAULT, self::FIRE_DELAY, self::ALIEN_VELOCITY_DEFAULT, $animationFrames);
            }
        }

        $this->aliveAliens = count($this->aliens);
        $this->initialAlienCount = $this->aliveAliens;
    }

    /**
     * @param int $xPosition
     * @param int $yPosition
     * @param int $fireChance
     * @param int $fireDelay
     * @param int $velocity
     * @param array $animationFrames
     */
    public function spawnMob($xPosition, $yPosition, $fireChance, $fireDelay, $velocity, array $animationFrames)
    {
        $alien = new Alien($xPosition, $yPosition, $fireChance, $fireDelay, $velocity, $animationFrames);
        $alien->setDirection($this->getAliensCurrentDirection());
        $this->aliens[] = $alien;
    }

    /**
     * @DI\Observe(Events::HEARTBEAT, priority = 0)
     *
     * @param HeartbeatEvent $event
     */
    public function updateAliens(HeartbeatEvent $event)
    {
        $changeDirections = false;

        /** @var Alien $alien */
        foreach ($this->aliens as $idx => $alien) {
            $alien->animate($event->getTimestamp());

            if ($event->getTimestamp() >= $alien->getLastUpdated() + $alien->getVelocity()) {
                $alien->setLastUpdated($event->getTimestamp());

                if ($alien->getDirection() == Alien::DIRECTION_LEFT) {
                    $alien->setXPosition($alien->getXPosition() - 1);
                } else {
                    $alien->setXPosition($alien->getXPosition() + 1);
                }
            }

            // Check to see if this alien has reached a border
            if ($alien->getDirection() == Alien::DIRECTION_LEFT && $alien->getXPosition() == 1) {
                if ($alien->getState() == Alien::STATE_FLEEING) {
                    unset($this->aliens[$idx]);
                    continue;
                }

                $changeDirections = true;
            } elseif ($alien->getDirection() == Alien::DIRECTION_RIGHT && $alien->getXPosition() == $this->boardWidth - 3) {
                if ($alien->getState() == Alien::STATE_FLEEING) {
                    unset($this->aliens[$idx]);
                    continue;
                }

                $changeDirections = true;
            }

            if ($alien->getState() == Alien::STATE_DYING && $event->getTimestamp() > $alien->getHitTimestamp() + $alien->getVelocity() * 5) {
                $alien->setState(Alien::STATE_DEAD);
                $this->aliveAliens--;
                $this->eventDispatcher->dispatch(Events::ALIEN_DEAD, new AlienDeadEvent($this->initialAlienCount, $this->aliveAliens, $this->aliens[$idx]));
                unset($this->aliens[$idx]);
            }

            // See if this alien can fire his weapon
            if ($this->projectileManager->getAlienProjectileCount() < $this->maxProjectiles && $event->getTimestamp() + $alien->getLastFired() > $alien->getFireDelay()) {
                if (rand(0, 10000) < $alien->getFireChance()) {
                    $alien->setLastFired($event->getTimestamp());
                    $this->projectileManager->fireAlienProjectile($alien->getXPosition(), $alien->getYPosition()+1, self::PROJECTILE_VELOCITY);
                }
            }
        }

        if ($changeDirections) {
            $newDirection = $this->getAliensCurrentDirection() == Alien::DIRECTION_RIGHT ? Alien::DIRECTION_LEFT : Alien::DIRECTION_RIGHT;
            foreach ($this->aliens as $alien) {
                $alien->setDirection($newDirection);
                $alien->setYPosition($alien->getYPosition() + 1);
                if ($alien->getState() == Alien::STATE_ALIVE && $alien->getYPosition() == $this->boardHeight - 2) {
                    $this->eventDispatcher->dispatch(Events::ALIEN_REACHED_END, new AlienReachedEndEvent());
                }
            }
        }
    }

    /**
     * @DI\Observe(Events::BOARD_REDRAW, priority = -200)
     *
     * @param RedrawEvent $event
     */
    public function redrawAliens(RedrawEvent $event)
    {
        $output = $event->getOutput();

        /** @var Alien $alien */
        foreach ($this->aliens as $alien) {
            $alienCharacter = $alien->getCurrentDisplayCharacter();

            switch ($alien->getState()) {
                case Alien::STATE_ALIVE:
                    $color = 'blue';
                    break;

                case Alien::STATE_MAD:
                    $color = 'green';
                    break;

                case Alien::STATE_FRENZY:
                    $color = 'red';
                    break;

                case Alien::STATE_DYING:
                    $color = 'yellow';
                    break;

                case Alien::STATE_FLEEING:
                    $color = 'magenta';
                    break;
                
                default:
                    $color = null;
                    break;
            }

            $output->putNextValue($alien->getXPosition(), $alien->getYPosition(), $alienCharacter, $color);
        }
    }

    /**
     * @DI\Observe(Events::PLAYER_PROJECTILES_UPDATED, priority = 0)
     *
     * @param PlayerProjectilesUpdatedEvent $event
     */
    public function testForCollisions(PlayerProjectilesUpdatedEvent $event)
    {
        $playerProjectiles = $event->getProjectiles();

        /** @var AbstractProjectile $projectile */
        foreach ($playerProjectiles as $idx => $projectile) {
            /** @var Alien $alien */
            foreach ($this->aliens as $alien) {
                if ($alien->getState() != Alien::STATE_DEAD && $projectile->getXPosition() == $alien->getXPosition() && $projectile->getYPosition() == $alien->getYPosition()) {
                    $alien->setState(Alien::STATE_DYING);
                    $alien->setHitTimestamp(microtime(true));
                    $this->eventDispatcher->dispatch(Events::ALIEN_HIT, new AlienHitEvent($idx));
                }
            }
        }

        if ($this->globalAlienState != Alien::STATE_FLEEING) {
            if ($this->globalAlienState == Alien::STATE_ALIVE && $this->aliveAliens <= ((int) $this->initialAlienCount / 2)) {
                $this->globalAlienState = Alien::STATE_MAD;
                $this->makeAliensMadder();
            } elseif ($this->globalAlienState == Alien::STATE_MAD && $this->aliveAliens <= ((int) $this->initialAlienCount / 8)) {
                $this->globalAlienState = Alien::STATE_FRENZY;
                $this->makeAliensMadder();
            }
        }
    }

    /**
     * @DI\Observe(Events::BOSS_DYING, priority = 0)
     *
     * @param BossDyingEvent $event
     */
    public function onBossDying(BossDyingEvent $event)
    {
        $this->globalAlienState = Alien::STATE_FLEEING;

        /** @var Alien $alien */
        foreach ($this->aliens as $alien) {
            $alien->setState(Alien::STATE_FLEEING);
            $alien->setDirection(rand(Alien::DIRECTION_LEFT, Alien::DIRECTION_RIGHT));
            $alien->setVelocity($alien->getVelocity() / 2);
        }
    }

    /**
     * @return int
     */
    public function getAlienCount()
    {
        return count($this->aliens);
    }

    /**
     * Called to make aliens faster etc
     */
    private function makeAliensMadder()
    {
        $aliveStates = [Alien::STATE_ALIVE, Alien::STATE_MAD];

        if ($this->globalAlienState == Alien::STATE_MAD) {
            $newVelocity = self::ALIEN_VELOCITY_DEFAULT / 2;
            $newDelay = self::FIRE_DELAY / 3;
            $fireChance = 100;
        } else {
            $newVelocity = self::ALIEN_VELOCITY_DEFAULT / 3;
            $newDelay = self::FIRE_DELAY / 10;
            $fireChance = 500;
        }

        /** @var Alien $alien */
        foreach ($this->aliens as $alien) {
            if (in_array($alien->getState(), $aliveStates)) {
                $alien->setState($this->globalAlienState);
                $alien->setVelocity($newVelocity);
                $alien->setFireDelay($newDelay);
                $alien->setFireChance($fireChance);
            }
        }
    }

    /**
     * @return int
     */
    private function getAliensCurrentDirection()
    {
        if (empty($this->aliens)) {
            return Alien::DIRECTION_RIGHT;
        }

        reset($this->aliens);
        $alien = current($this->aliens);

        return $alien->getDirection();
    }
}
