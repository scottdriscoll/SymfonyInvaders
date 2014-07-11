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
    const PROJECTILE_VELOCITY = .115;

    /**
     * @var int
     */
    const MAX_PROJECTILES = 10;

    /**
     * Percent chance per heartbeat that the aliens will fire
     *
     * @var int
     */
    const FIRE_CHANCE_DEFAULT = 1;

    /**
     * @var double
     */
    const FIRE_DELAY = 2.5;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var array
     */
    private $aliens = [];

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
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param int $numAlienRows
     * @param int $numAlienColumns
     * @param int $boardWidth
     * @param int $boardHeight
     */
    public function initialize($numAlienRows, $numAlienColumns, $boardWidth, $boardHeight)
    {
        for ($i = 0; $i < $numAlienRows; $i++) {
            for ($j = 1; $j <= $numAlienColumns * 2; $j += 2) {
                $this->aliens[] = new Alien($j, $i, self::FIRE_CHANCE_DEFAULT, self::FIRE_DELAY, self::ALIEN_VELOCITY_DEFAULT);
            }
        }

        $this->boardWidth = $boardWidth;
        $this->boardHeight = $boardHeight;
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

            // See if this alien can fire his weapon
            if ($alien->getState() != Alien::STATE_DEAD && count($this->alienProjectiles) < self::MAX_PROJECTILES && $alien->getFireDelay() + $event->getTimestamp() > $alien->getLastFired()) {
                if (rand(0, 100) < $alien->getFireChance()) {
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
                    $this->eventDispatcher->dispatch(Events::ALIEN_PROJECTILE_END, new AlienProjectileEndEvent($alien->getXPosition()));
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

        /** @var Alien $alien */
        foreach ($this->aliens as $alien) {
            $output->moveCursorDown($this->boardHeight);
            $output->moveCursorFullLeft();
            $output->moveCursorUp($this->boardHeight - $alien->getYPosition() - 1);
            $output->moveCursorRight($alien->getXPosition());

            switch ($alien->getState()) {
                case Alien::STATE_ALIVE:
                    $string = '<fg=blue>X</fg=blue> ';
                    break;

                case Alien::STATE_DYING:
                    $string = '<fg=red>X</fg=red> ';
                    break;

                default:
                    $string = '  ';
                    break;
            }

            $output->write($string);
        }

        /** @var Projectile $projectile */
        foreach ($this->alienProjectiles as $projectile) {
            $output->moveCursorDown($this->boardHeight);
            $output->moveCursorFullLeft();
            $output->moveCursorUp($this->boardHeight - $projectile->getYPosition() - 1);
            $output->moveCursorRight($projectile->getXPosition());
            $output->write('<fg=red>|</fg=red>');
        }
    }
}
