<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\InvadersBundle\Events;
use SD\InvadersBundle\Event\AliensInitializedEvent;

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
     */
    public function initialize($numAlienRows, $numAlienColumns)
    {

        $this->eventDispatcher->dispatch(Events::ALIENS_INITIALIZED, new AliensInitializedEvent());
    }
}
