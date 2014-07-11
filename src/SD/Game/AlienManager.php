<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\InvadersBundle\Events;
use SD\InvadersBundle\Event\AliensUpdatedEvent;
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
    const ALIEN_VELOCITY_DEFAULT = 1.0;

    /**
     * @var double
     */
    const PROJECTILE_VELOCITY = 0.025;

    /**
     * Percent chance per heartbeat that the aliens will fire
     *
     * @var int
     */
    const FIRE_CHANCE_DEFAULT = 2;

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
    private $boardSize;

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
     * @param int $boardSize
     */
    public function initialize($numAlienRows, $numAlienColumns, $boardSize)
    {
        for ($i = 0; $i < $numAlienRows; $i++) {
            for ($j = 1; $j <= $numAlienColumns * 2; $j += 2) {
                $this->aliens[] = new Alien($j, $i, self::FIRE_CHANCE_DEFAULT, self::ALIEN_VELOCITY_DEFAULT);
            }
        }

        $this->boardSize = $boardSize;
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

        /** @var Alien $alien */
        foreach ($this->aliens as $alien) {

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
            $output->moveCursorDown($this->boardSize);
            $output->moveCursorFullLeft();
            $output->moveCursorUp($this->boardSize - $alien->getYPosition() - 1);
            $output->moveCursorRight($alien->getXPosition());
            $output->write('<fg=blue>X</fg=blue>');
        }
    }
}
