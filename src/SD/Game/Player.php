<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use SD\InvadersBundle\Events;
use SD\InvadersBundle\Event\PlayerMoveLeftEvent;
use SD\InvadersBundle\Event\PlayerMoveRightEvent;
use SD\InvadersBundle\Event\RedrawEvent;
use SD\InvadersBundle\Event\PlayerFireEvent;
use SD\InvadersBundle\Event\AlienProjectileEndEvent;
use SD\InvadersBundle\Event\PlayerHitEvent;
use SD\InvadersBundle\Event\PowerupReachedEndEvent;

/**
 * @DI\Service("game.player")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
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
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "projectileManager" = @DI\Inject("game.projectile.manager"),
     *     "boardWidth" = @DI\Inject("%board_width%"),
     *     "boardHeight" = @DI\Inject("%board_height%")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param ProjectileManager $projectileManager
     * @param int $boardWidth
     * @param int $boardHeight
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ProjectileManager $projectileManager, $boardWidth, $boardHeight)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->projectileManager = $projectileManager;
        $this->minimumXPosition = 0;
        $this->maximumXPosition = $boardWidth - 3;
        $this->currentXPosition = (int) $boardWidth / 2;
        $this->yPosition = $boardHeight - 2;
    }

    /**
     * @DI\Observe(Events::PLAYER_MOVE_LEFT, priority = 0)
     *
     * @param PlayerMoveLeftEvent $event
     */
    public function moveLeft(PlayerMoveLeftEvent $event)
    {
        if ($this->currentXPosition > $this->minimumXPosition) {
            $this->currentXPosition--;
        }
    }

    /**
     * @DI\Observe(Events::PLAYER_MOVE_RIGHT, priority = 0)
     *
     * @param PlayerMoveRightEvent $event
     */
    public function moveRight(PlayerMoveRightEvent $event)
    {
        if ($this->currentXPosition < $this->maximumXPosition) {
            $this->currentXPosition++;
        }
    }

    /**
     * @DI\Observe(Events::PLAYER_FIRE, priority = 0)
     *
     * @param PlayerFireEvent $event
     */
    public function fire(PlayerFireEvent $event)
    {
        for ($i = 0; $i <= $this->currentWeaponState && $i <= self::WEAPON_STATE_MAXED; $i++) {
            if ($i % 2 == 1) {
                $offset = floor(-1 * $i / 2);
            } else {
                $offset = ceil($i / 2);
            }
            $this->projectileManager->firePlayerProjectile($this->currentXPosition + ($this->currentWeaponState == 0 ? 0 : 1) + $offset , $this->yPosition - 1, self::PROJECTILE_VELOCITY);
        }
    }

    /**
     * @DI\Observe(Events::ALIEN_PROJECTILE_END, priority = 0)
     *
     * @param AlienProjectileEndEvent $event
     */
    public function alienProjectileReachedEnd(AlienProjectileEndEvent $event)
    {
        $projectilePosition = $event->getXPosition();

        if ($projectilePosition >= $this->currentXPosition && $projectilePosition <= $this->currentXPosition + $this->currentWeaponState) {
            if ($this->currentShieldState == self::SHIELD_STATE_UPGRADED) {
                $this->currentShieldState = self::SHIELD_STATE_DEFAULT;
            } elseif ($this->currentWeaponState > 0){
                $this->currentWeaponState--;
            } else {            
                $this->eventDispatcher->dispatch(Events::PLAYER_HIT, new PlayerHitEvent());
            }
        }
    }

    /**
     * @DI\Observe(Events::BOARD_REDRAW, priority = 0)
     *
     * @param RedrawEvent $event
     */
    public function redrawPlayer(RedrawEvent $event)
    {
        $output = $event->getOutput();

        // Reset cursor to a known position
        $output->moveCursorDown($this->yPosition + 1);
        $output->moveCursorFullLeft();

        // Move to proper location
        $output->moveCursorUp(2);
        $output->moveCursorRight($this->currentXPosition);
        $color = $this->currentShieldState == self::SHIELD_STATE_UPGRADED ? 'blue' : 'white';
        $output->write(sprintf("<fg=%s>%s</fg=%s>", $color, $this->shipStyles[$this->currentWeaponState], $color));
    }

    /**
     * @DI\Observe(Events::POWERUP_REACHED_END, priority = 0)
     *
     * @param PowerupReachedEndEvent $event
     */
    public function powerupReachedEnd(PowerupReachedEndEvent $event)
    {
        $powerupPosition = $event->getPowerup()->getXPosition();

        if ($powerupPosition >= $this->currentXPosition && $powerupPosition <= $this->currentXPosition + $this->currentWeaponState) {
            if (get_class($event->getPowerup()) == 'SD\Game\Powerup\WeaponPowerup') {
                if ($this->currentWeaponState < self::WEAPON_STATE_MAXED) {
                    $this->currentWeaponState++;
                }
            } else {
                $this->currentShieldState = self::SHIELD_STATE_UPGRADED;
            }
        }
    }
}
