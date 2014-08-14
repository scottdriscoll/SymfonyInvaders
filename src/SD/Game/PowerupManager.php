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
use SD\InvadersBundle\Event\PowerupActivatedEvent;
use SD\InvadersBundle\Event\PlayerHitEvent;
use SD\Game\Powerup\AbstractPowerup;
use SD\Game\Player;

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
     *     "player" = @DI\Inject("game.player"),
     *     "boardHeight" = @DI\Inject("%board_height%")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param Player $player
     * @param int $boardHeight
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, Player $player, $boardHeight)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->boardHeight = $boardHeight;
        $this->player = $player;
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
                    if (!$powerup->isActivated()) {
                        unset($this->powerups[$idx]);
                    }
                }
            }
        }
    }

    /**
     * @DI\Observe(Events::PLAYER_HIT, priority = 0)
     *
     * @param PlayerHitEvent $event
     */
    public function playerHit(PlayerHitEvent $event)
    {
        $highestPrioriylosablePower = null;
        $index = null;
        /** @var AbstractPowerup $powerup */
        foreach ($this->powerups as $idx => $powerup) {
            if ($powerup->isActivated() && $powerup->isLosable() && (empty($highestPrioriylosablePower) || $highestPrioriylosablePower->getPriority() < $powerup->getPriority())) {
                $highestPrioriylosablePower = $powerup;
                $index = $idx;
            }
        }        

        if (!empty($highestPrioriylosablePower)) {
            $highestPrioriylosablePower->unApplyUpgradeToPlayer($this->player);
            unset($this->powerups[$index]);
        } else {
            $this->player->removeHealth(1);
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
        foreach ($this->powerups as $idx => $powerup) {
            if ($powerup->isActivated()) {
                $powerup->drawActivated($output, $this->player);
            } else {
                $output->moveCursorDown($this->boardHeight);
                $output->moveCursorFullLeft();
                $output->moveCursorUp($this->boardHeight - $powerup->getYPosition());
                $output->moveCursorRight($powerup->getXPosition());
                $powerup->draw($output);
            }
        }
        $this->player->resetHeightLayers();
        $this->player->resetWidthLayers();
    }

    /**
     * @DI\Observe(Events::POWERUP_ACTIVATED, priority = 0)
     *
     * @param PowerupActivatedEvent $event
     */
    public function drawPowerupActivation(PowerupActivatedEvent $event)
    {
        $event->getPowerup()->applyUpgradeToPlayer($event->getPlayer());
    }   
    
    /**
     * @DI\Observe(Events::ALIEN_DEAD, priority = 0)
     *
     * @param AlienDeadEvent $event
     */
    public function alienKilled(AlienDeadEvent $event)
    {
        $types = array('Speed', 'Weapon', 'Shield');
        if (rand(0, 100) < self::DROP_CHANCE) {
            $type = $types[rand(0 , 2)];
            $class = '\SD\Game\Powerup\\' . $type . 'Powerup';
            
            $this->powerups[] = new $class($event->getAlien()->getXPosition(), $event->getAlien()->getYPosition(), $this->eventDispatcher);
        }
    }
}
