<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use SD\InvadersBundle\Events;
use SD\InvadersBundle\Event\HeartbeatEvent;
use SD\InvadersBundle\Event\RedrawEvent;
use SD\InvadersBundle\Event\AlienDeadEvent;
use SD\InvadersBundle\Event\PowerupReachedEndEvent;
use SD\Game\Powerup\AbstractPowerup;

/**
 * @DI\Service("game.powerup.manager")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class PowerupManager
{
    /**
     * @var int
     */
    const DROP_CHANCE = 7;

    /**
     * @var double
     */
    const VELOCITY = .0500;

    /**
     * @var array
     */
    private $powerups = [];

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

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
     * @DI\Observe(Events::HEARTBEAT, priority = 0)
     *
     * @param HeartbeatEvent $event
     */
    public function updatePowerups(HeartbeatEvent $event)
    {
        /** @var AbstractPowerup $powerup */
        foreach ($this->powerups as $idx => $powerup) {
            if ($event->getTimestamp() >= $powerup->getLastUpdate() + self::VELOCITY) {
                $powerup->setLastUpdate($event->getTimestamp());
                $powerup->setYPosition($powerup->getYPosition() + 1);
                if ($powerup->getYPosition() == $this->boardHeight - 1) {
                    $this->eventDispatcher->dispatch(Events::POWERUP_REACHED_END, new PowerupReachedEndEvent($powerup));
                    unset($this->powerups[$idx]);
                }
            }
        }
    }

    /**
     * @DI\Observe(Events::BOARD_REDRAW, priority = 0)
     *
     * @param RedrawEvent $event
     */
    public function drawPowerups(RedrawEvent $event)
    {
        $output = $event->getOutput();

        /** @var AbstractPowerup $powerup */
        foreach ($this->powerups as $powerup) {
            $output->moveCursorDown($this->boardHeight);
            $output->moveCursorFullLeft();
            $output->moveCursorUp($this->boardHeight - $powerup->getYPosition());
            $output->moveCursorRight($powerup->getXPosition());
            $powerup->draw($output);
        }
    }

    /**
     * @DI\Observe(Events::ALIEN_DEAD, priority = 0)
     *
     * @param AlienDeadEvent $event
     */
    public function alienKilled(AlienDeadEvent $event)
    {
        if (rand(0, 100) < self::DROP_CHANCE) {
            $class = rand(0,1) == 1 ? '\SD\Game\Powerup\WeaponPowerup' : '\SD\Game\Powerup\ShieldPowerup';

            $this->powerups[] = new $class($event->getAlien()->getXPosition(), $event->getAlien()->getYPosition());
        }
    }
}
