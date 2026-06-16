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
use App\Event\AlienDeadEvent;
use App\Event\PowerupReachedEndEvent;
use App\Event\PowerupActivatedEvent;
use App\Event\PlayerHitEvent;
use App\Game\Powerup\AbstractPowerup;
use App\Game\Powerup\ShieldPowerup;
use App\Game\Powerup\SpeedPowerup;
use App\Game\Powerup\WeaponPowerup;

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
     * @var Player
     */
    private $player;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Player $player,
        #[Autowire('%board_height%')]
        $boardHeight,
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->boardHeight = $boardHeight;
        $this->player = $player;
    }

    #[AsEventListener]
    public function updatePowerups(HeartbeatEvent $event)
    {
        /** @var AbstractPowerup $powerup */
        foreach ($this->powerups as $idx => $powerup) {
            if ($event->getTimestamp() >= $powerup->getLastUpdate() + self::VELOCITY) {
                $powerup->setLastUpdate($event->getTimestamp());
                $powerup->setYPosition($powerup->getYPosition() + 1);
                if ($powerup->getYPosition() == $this->boardHeight - 1) {
                    $this->eventDispatcher->dispatch(new PowerupReachedEndEvent($powerup));
                    if (!$powerup->isActivated()) {
                        unset($this->powerups[$idx]);
                    }
                }
            }
        }
    }

    #[AsEventListener]
    public function playerHit(PlayerHitEvent $event)
    {
        /** @var AbstractPowerup $highestPriorityLosablePower */
        $highestPriorityLosablePower = null;
        $index = null;
        /** @var AbstractPowerup $powerup */
        foreach ($this->powerups as $idx => $powerup) {
            if ($powerup->isActivated() && $powerup->isLosable() && (empty($highestPriorityLosablePower) || $highestPriorityLosablePower->getPriority() < $powerup->getPriority())) {
                $highestPriorityLosablePower = $powerup;
                $index = $idx;
            }
        }        

        if (!empty($highestPriorityLosablePower)) {
            $highestPriorityLosablePower->unApplyUpgradeToPlayer($this->player);
            unset($this->powerups[$index]);
        } else {
            $this->player->removeHealth(1);
        }
    }    
    #[AsEventListener]
    public function drawPowerups(RedrawEvent $event)
    {
        $output = $event->getOutput();

        /** @var AbstractPowerup $powerup */
        foreach ($this->powerups as $powerup) {
            if ($powerup->isActivated()) {
                $powerup->drawActivated($output, $this->player);
            } else {
                $powerup->draw($output);
            }
        }
        $this->player->resetHeightLayers();
        $this->player->resetWidthLayers();
    }

    #[AsEventListener]
    public function drawPowerupActivation(PowerupActivatedEvent $event)
    {
        $event->getPowerup()->applyUpgradeToPlayer($event->getPlayer());
    }   
    
    #[AsEventListener]
    public function alienKilled(AlienDeadEvent $event)
    {
        $types = [
            SpeedPowerup::class,
            WeaponPowerup::class,
            ShieldPowerup::class,
        ];
        if (rand(0, 100) < self::DROP_CHANCE) {
            $class = $types[rand(0, 2)];
            
            $this->powerups[] = new $class($event->getAlien()->getXPosition(), $event->getAlien()->getYPosition(), $this->eventDispatcher);
        }
    }
}
