<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\InvadersBundle\Events;
use SD\InvadersBundle\Event\AliensUpdatedEvent;
use SD\InvadersBundle\Event\AlienProjectileEndEvent;
use SD\InvadersBundle\Event\HeartbeatEvent;
use SD\InvadersBundle\Event\RedrawEvent;
use SD\InvadersBundle\Event\PlayerProjectilesUpdatedEvent;
use SD\InvadersBundle\Event\AlienHitEvent;
use SD\InvadersBundle\Event\AlienDeadEvent;

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
     * @var array
     */
    private $alienProjectiles = [];

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
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "boardWidth" = @DI\Inject("%board_width%"),
     *     "boardHeight" = @DI\Inject("%board_height%"),
     *     "numAlienColumns" = @DI\Inject("%alien_columns%"),
     *     "numAlienRows" = @DI\Inject("%alien_rows%"),
     *     "maxProjectiles" = @DI\Inject("%max_alien_projectiles%")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param int $boardWidth
     * @param int $boardHeight
     * @param int $numAlienColumns
     * @param int $numAlienRows
     * @param int $maxProjectiles
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $boardWidth, $boardHeight, $numAlienColumns, $numAlienRows, $maxProjectiles)
    {
        $this->eventDispatcher = $eventDispatcher;
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
                $this->aliens[] = new Alien($j, $i, self::DEFAULT_FIRE_CHANCE_DEFAULT, self::FIRE_DELAY, self::ALIEN_VELOCITY_DEFAULT, $animationFrames);
            }
        }

        $this->aliveAliens = count($this->aliens);
        $this->eventDispatcher->dispatch(Events::ALIENS_UPDATED, new AliensUpdatedEvent());
    }

    /**
     * @DI\Observe(Events::HEARTBEAT, priority = 0)
     *
     * @param HeartbeatEvent $event
     */
    public function updateAliensAndProjectiles(HeartbeatEvent $event)
    {
        $updated = false;
        $changeDirections = false;

        /** @var Alien $alien */
        foreach ($this->aliens as $alien) {
            $alien->animate($event->getTimestamp());

            if ($event->getTimestamp() >= $alien->getLastUpdated() + $alien->getVelocity()) {
                $alien->setLastUpdated($event->getTimestamp());

                if ($alien->getDirection() == Alien::DIRECTION_LEFT) {
                    $alien->setXPosition($alien->getXPosition() - 1);
                } else {
                    $alien->setXPosition($alien->getXPosition() + 1);
                }

                $updated = true;
            }

            // Check to see if this alien has reached a border
            if ($alien->getState() != Alien::STATE_DEAD) {
                if ($alien->getDirection() == Alien::DIRECTION_LEFT && $alien->getXPosition() == 1) {
                    $changeDirections = true;
                } elseif ($alien->getDirection() == Alien::DIRECTION_RIGHT && $alien->getXPosition() == $this->boardWidth - 3) {
                    $changeDirections = true;
                }
            }

            if ($alien->getState() == Alien::STATE_DYING && $event->getTimestamp() > $alien->getHitTimestamp() + $alien->getVelocity() * 5) {
                $alien->setState(Alien::STATE_DEAD);
                $this->aliveAliens--;
                $this->eventDispatcher->dispatch(Events::ALIEN_DEAD, new AlienDeadEvent(count($this->aliens), $this->aliveAliens));
            }

            // See if this alien can fire his weapon
            if ($alien->getState() != Alien::STATE_DEAD && count($this->alienProjectiles) < $this->maxProjectiles && $event->getTimestamp() + $alien->getLastFired() > $alien->getFireDelay()) {
                if (rand(0, 10000) < $alien->getFireChance()) {
                    $alien->setLastFired($event->getTimestamp());
                    $this->alienProjectiles[] = new Projectile($alien->getXPosition(), $alien->getYPosition(), $event->getTimestamp(), self::PROJECTILE_VELOCITY);
                }
            }
        }

        if ($changeDirections) {
            $newDirection = $this->aliens[0]->getDirection() == Alien::DIRECTION_RIGHT ? Alien::DIRECTION_LEFT : Alien::DIRECTION_RIGHT;
            foreach ($this->aliens as $alien) {
                $alien->setDirection($newDirection);
                $alien->setYPosition($alien->getYPosition() + 1);
            }
        }

        // Update projectiles
        /** @var Projectile $projectile */
        foreach ($this->alienProjectiles as $idx => $projectile) {
            if ($event->getTimestamp() >= $projectile->getLastUpdatedTime() + $projectile->getVelocity()) {
                $updated = true;
                $projectile->setYPosition($projectile->getYPosition() + 1);
                $projectile->setLastUpdatedTime($event->getTimestamp());
                if ($projectile->getYPosition() == $this->boardHeight - 2) {
                    $this->eventDispatcher->dispatch(Events::ALIEN_PROJECTILE_END, new AlienProjectileEndEvent($projectile->getXPosition()));
                    unset($this->alienProjectiles[$idx]);
                }
            }
        }

        if ($updated) {
            $this->eventDispatcher->dispatch(Events::ALIENS_UPDATED, new AliensUpdatedEvent());
        }
    }

    /**
     * @DI\Observe(Events::BOARD_REDRAW, priority = 0)
     *
     * @param RedrawEvent $event
     */
    public function redrawAliensAndProjectiles(RedrawEvent $event)
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

        /** @var Alien $alien */
        foreach ($this->aliens as $alien) {
            $output->moveCursorDown($this->boardHeight);
            $output->moveCursorFullLeft();
            $output->moveCursorUp($this->boardHeight - $alien->getYPosition() - 1);
            $output->moveCursorRight($alien->getXPosition());
            $alienCharacter = $alien->getCurrentDisplayCharacter();

            switch ($alien->getState()) {
                case Alien::STATE_ALIVE:
                    $string = '<fg=blue>' . $alienCharacter . '</fg=blue> ';
                    break;

                case Alien::STATE_MAD:
                $string = '<fg=green>' . $alienCharacter . '</fg=green> ';
                break;

                case Alien::STATE_FRENZY:
                    $string = '<fg=red>' . $alienCharacter . '</fg=red> ';
                    break;

                case Alien::STATE_DYING:
                    $string = '<fg=yellow>' . $alienCharacter. '</fg=yellow> ';
                    break;

                default:
                    $string = '  ';
                    break;
            }

            $output->write($string);
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

        /** @var Projectile $projectile */
        foreach ($playerProjectiles as $idx => $projectile)
        {
            /** @var Alien $alien */
            foreach ($this->aliens as $alien) {
                if ($alien->getState() != Alien::STATE_DEAD && $projectile->getXPosition() == $alien->getXPosition() && $projectile->getYPosition() == $alien->getYPosition()) {
                    $alien->setState(Alien::STATE_DYING);
                    $alien->setHitTimestamp(microtime(true));
                    $this->eventDispatcher->dispatch(Events::ALIEN_HIT, new AlienHitEvent($idx));
                    break;
                }
            }
        }

        if ($this->globalAlienState == Alien::STATE_ALIVE && $this->aliveAliens <= ((int) count($this->aliens) / 3)) {
            $this->globalAlienState = Alien::STATE_MAD;
            $this->aliensMadder();
        } elseif ($this->globalAlienState == Alien::STATE_MAD && $this->aliveAliens <= ((int) count($this->aliens) / 8)) {
            $this->globalAlienState = Alien::STATE_FRENZY;
            $this->aliensMadder();
        }
    }

    /**
     * Called to make aliens faster etc
     */
    private function aliensMadder()
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
}
