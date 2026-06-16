<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Game;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Event\PlayerMoveLeftEvent;
use App\Event\PlayerMoveRightEvent;
use App\Event\PowerupActivatedEvent;
use App\Event\RedrawEvent;
use App\Event\PlayerFireEvent;
use App\Event\AlienProjectileEndEvent;
use App\Event\PlayerHitEvent;
use App\Event\PowerupReachedEndEvent;

class Player
{
    /**
     * @var int
     */
    const WEAPON_STATE_DEFAULT = 0;

    /**
     * @var int
     */
    const WEAPON_STATE_MAXED = 4;
    
    /**
     * @var int
     */
    const SPEED_STATE_MAXED = 2;
    
    /**
     * @var int
     */
    const SHIELD_STATE_MAXED = 3;    
    
    const SHIELD_STATE_DEFAULT = 0;

    const SHIELD_STATE_UPGRADED = 1;

    /**
     * @var double
     */
    const PROJECTILE_VELOCITY = 0.025;

    /**
     * @var array
     */
    private $shipStyles = ['^', '^^', '^^^', '^^^^', '^^^^^'];

    /**
     * @var int
     */
    private $health = 1;
    /**
     * @var int
     */
    private $currentXPosition;

    /**
     * @var int
     */
    private $yPosition;

    /**
     * @var int
     */
    private $minimumXPosition;

    /**
     * @var int
     */
    private $maximumXPosition;

    /**
     * @var ProjectileManager
     */
    private $projectileManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var int
     */
    private $currentWeaponState = self::WEAPON_STATE_DEFAULT;

    /**
     * @var int
     */
    private $currentShieldState = self::SHIELD_STATE_DEFAULT;

    /**
     * @var int
     */    
    private $currentSpeedState = 0;
    
    /**
     * @var int
     */
    private $baseWidth = 1;
    
    /**
     * @var int
     */
    private $baseHeight = 1;    
    
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ProjectileManager $projectileManager,
        #[Autowire('%board_width%')]
        $boardWidth,
        #[Autowire('%board_height%')]
        $boardHeight,
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->projectileManager = $projectileManager;
        $this->minimumXPosition = 0;
        $this->maximumXPosition = $boardWidth - 2;
        $this->currentXPosition = (int) $boardWidth / 2;
        $this->yPosition = $boardHeight - 2;
    }

    public function getXPosition()
    {
        return $this->currentXPosition;
    }
    
    public function getYPosition()
    {
        return $this->yPosition + 1;
    }
    
    public function getWidth()
    {
        return $this->currentWeaponState + $this->baseWidth;
    }
    
    public function addWidth($width)
    {
        $this->baseWidth += $width;
    }
    
    public function removeWidth($width)
    {
        $this->baseWidth -= $width;
    }
    
    public function resetWidthLayers()
    {
        $this->baseWidth = 1;
    }
    
    public function getHeight()
    {
        return $this->baseHeight;
    }

    public function addHeight($height)
    {
        $this->baseHeight += $height;
    }
    
    public function removeHeight($height)
    {
        $this->baseHeight -= $height;
    }
    
    public function addHeightLayer()
    {
        $this->baseHeight++;
    }
    
    public function resetHeightLayers()
    {
        $this->baseHeight = 1;
    }
    
    public function getHealth()
    {
        return $this->health;
    }
    
    public function removeHealth($amount = 1)
    {
        $this->health -= $amount;
    }
    #[AsEventListener]
    public function moveLeft(PlayerMoveLeftEvent $event)
    {
        if (($this->currentXPosition - $this->currentSpeedState) > $this->minimumXPosition) {
            $this->currentXPosition -= (1 + $this->currentSpeedState);
        }
    }

    #[AsEventListener]
    public function moveRight(PlayerMoveRightEvent $event)
    {
        if ($this->currentXPosition + $this->currentSpeedState + $this->currentWeaponState < $this->maximumXPosition) {
            $this->currentXPosition += (1 + $this->currentSpeedState);
        }
    }

    #[AsEventListener]
    public function fire(PlayerFireEvent $event)
    {
        for ($i = 0; $i <= $this->currentWeaponState && $i <= self::WEAPON_STATE_MAXED; $i++) {
            $this->projectileManager->firePlayerProjectile($this->currentXPosition + $i , $this->yPosition - 1, self::PROJECTILE_VELOCITY / (1 + $this->currentSpeedState));
        }
    }

    #[AsEventListener]
    public function alienProjectileReachedEnd(AlienProjectileEndEvent $event)
    {
        $projectilePosition = $event->getXPosition();

        if ($projectilePosition >= $this->currentXPosition && $projectilePosition <= $this->currentXPosition + $this->currentWeaponState) {
            $this->eventDispatcher->dispatch(new PlayerHitEvent());
        }
    }

    #[AsEventListener]
    public function redrawPlayer(RedrawEvent $event)
    {
        $output = $event->getOutput();

        $color = $this->currentShieldState > self::SHIELD_STATE_DEFAULT ? 'blue' : null;
        $output->putArrayOfValues($this->currentXPosition, $this->yPosition, array($this->shipStyles[$this->currentWeaponState]), $color);      
    }

    #[AsEventListener]
    public function powerupReachedEnd(PowerupReachedEndEvent $event)
    {
        $powerupPosition = $event->getPowerup()->getXPosition();

        if (($powerupPosition >= $this->currentXPosition 
                && ($powerupPosition <= $this->currentXPosition + $this->currentWeaponState)
            ) 
            || 
            ($powerupPosition >= $this->currentXPosition - ceil($this->currentSpeedState / 2)
                && $powerupPosition <= $this->currentXPosition + ceil($this->currentSpeedState / 2)
            )
        ) {
            $this->eventDispatcher->dispatch(new PowerupActivatedEvent($event->getPowerup(), $this));
        }
    }
    
    public function addShield()
    {
        if ($this->currentShieldState < self::SHIELD_STATE_MAXED) {
            $this->currentShieldState++;
            
            return true;
        }

        return false;
    }
    
    public function removeShield()
    {
        if ($this->currentShieldState > self::SHIELD_STATE_DEFAULT) {
            $this->currentShieldState--;
            
            return true;
        }

        return false;
    }
    
    public function addWeapon()
    {
        if ($this->currentWeaponState < self::WEAPON_STATE_MAXED) {
            $this->currentWeaponState++;
            
            return true;
        }

        return false;
    }
    
    public function removeWeapon()
    {
        if ($this->currentWeaponState > self::WEAPON_STATE_DEFAULT) {
            $this->currentWeaponState--;
            
            return true;
        }

        return false;
    }
    
    public function addSpeed()
    {
        if ($this->currentSpeedState < self::SPEED_STATE_MAXED) {
            $this->currentSpeedState++;
            
            return true;
        }

        return false;
    }
}
