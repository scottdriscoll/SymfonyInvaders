<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\ConsoleHelper\OutputHelper;
use SD\ConsoleHelper\ScreenBuffer;
use SD\InvadersBundle\Events;
use SD\InvadersBundle\Event\AlienReachedEndEvent;
use SD\InvadersBundle\Event\RedrawEvent;
use SD\InvadersBundle\Event\AlienDeadEvent;
use SD\InvadersBundle\Event\PlayerHitEvent;
use SD\InvadersBundle\Event\PlayerFireEvent;
use SD\InvadersBundle\Event\GameOverEvent;
use SD\InvadersBundle\Event\BossHitEvent;
use SD\InvadersBundle\Event\BossDeadEvent;
use SD\InvadersBundle\Event\HeartbeatEvent;
use SD\Game\Player;

/**
 * @DI\Service("game.board")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class Board
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var OutputHelper
     */
    private $output;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var int
     */
    private $shotsFired =0;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Boss
     */
    private $boss;
    
    /**
     * @var Player
     */
    private $player;
    
    /**
     * @var ScreenBuffer
     */
    private $buffer;    

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "boss" = @DI\Inject("game.boss"),
     *     "player" = @DI\Inject("game.player"),
     *     "buffer" = @DI\Inject("screen_buffer"),
     *     "width" = @DI\Inject("%board_width%"),
     *     "height" = @DI\Inject("%board_height%")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param Boss $boss
     * @param Player $player
     * @param ScreenBuffer $buffer
     * @param int $width
     * @param int $height
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, Boss $boss, Player $player, ScreenBuffer $buffer, $width, $height)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->boss = $boss;
        $this->player = $player;
        $this->buffer = $buffer;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @param OutputHelper $output
     */
    public function initialize(OutputHelper $output)
    {
        $this->output = $output;
        $this->buffer->initialize($this->width, $this->height + 1);

        $this->initialized = true;

        if (!empty($this->message)) {
            $this->rewriteMessage($this->message);         
        }
        $this->buffer->paintChanges($this->output);
        $this->buffer->nextFrame();          
        $this->output->dump();
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;

        if ($this->initialized) {
            $this->rewriteMessage();
        }
    }
  
    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }    

    /**
     * @DI\Observe(Events::ALIEN_DEAD, priority = 0)
     *
     * @param AlienDeadEvent $event
     */
    public function alienHit(AlienDeadEvent $event)
    {
        if ($this->boss->getState() == Boss::STATE_DYING) {
            $this->setMessage('The invaders are fleeing!');
        } elseif ($event->getAliveAliens() == 0) {
            if (!$this->boss->isAlive()) {
                $this->boss->spawnBoss($this->width, $this->height);
            }
        } elseif (!$this->boss->isAlive()) {
            $output = 'Aliens remaining: ' . $event->getAliveAliens() . '/' . $event->getTotalAliens();
            $this->setMessage($output);
        }
    }
        /**
     * @DI\Observe(Events::BOSS_DEAD, priority = 0)
     *
     * @param BossDeadEvent $event
     */
    public function bossDead(BossDeadEvent $event)
    {
        $this->setMessage("You win!! Total shots fired: " . $this->shotsFired . "\n");
        $this->eventDispatcher->dispatch(Events::GAME_OVER, new GameOverEvent());
    }

    /**
     * @DI\Observe(Events::PLAYER_HIT, priority = -255)
     *
     * @param PlayerHitEvent $event
     */
    public function playerHit(PlayerHitEvent $event)
    {
        if ($this->player->getHealth() < 1) {
            $this->setMessage("You were killed!! Total shots fired: " . $this->shotsFired . "\n");
            $this->eventDispatcher->dispatch(Events::GAME_OVER, new GameOverEvent());
        }
    }

    /**
     * @DI\Observe(Events::ALIEN_REACHED_END, priority = 0)
     *
     * @param AlienReachedEndEvent $event
     */
    public function alienReachedEnd(AlienReachedEndEvent $event)
    {
        $this->setMessage("An invader reached your home!! Total shots fired: " . $this->shotsFired . "\n");
        $this->eventDispatcher->dispatch(Events::GAME_OVER, new GameOverEvent());
    }

    /**
     * @DI\Observe(Events::PLAYER_FIRE, priority = 0)
     *
     * @param PlayerFireEvent $event
     */
    public function playerFired(PlayerFireEvent $event)
    {
        $this->shotsFired++;
    }
     /**
     * @DI\Observe(Events::BOSS_HIT, priority = 0)
     *
     * @param BossHitEvent $event
     */
    public function bossHit(BossHitEvent $event)
    {
        $msg = "Boss health: " . $event->getHealth();
        $this->setMessage($msg);
    }
    /**
     * @DI\Observe(Events::HEARTBEAT, priority = -255)
     *
     * @param HeartbeatEvent $event
     */
    public function redrawBoard(HeartbeatEvent $event)
    {
        $this->output->clear();
        $this->buffer->clearScreen();

        //bottom line
        for ($i = 0; $i < $this->width; $i++) {
            $this->buffer->putNextValue($i, $this->height - 1, '<fg=yellow>-</fg=yellow>');
        }
        //top line
        for ($i = 0; $i < $this->width; $i++) {
            $this->buffer->putNextValue($i, 0, '<fg=yellow>-</fg=yellow>');
        }
        
        for ($i = 0; $i < $this->height; $i++) {
            $this->buffer->putNextValue(0, $i, '<fg=yellow>|</fg=yellow>');
        }  
        
        for ($i = 0; $i < $this->height; $i++) {
            $this->buffer->putNextValue($this->width - 1, $i, '<fg=yellow>|</fg=yellow>');
        }          
        
        //pass buffer instead of output
        $this->eventDispatcher->dispatch(Events::BOARD_REDRAW, new RedrawEvent($this->buffer));

        $this->rewriteMessage();
        $this->buffer->paintChanges($this->output);
        $this->buffer->nextFrame();

        $this->output->dump();
    }

    /**
     * Erases old message and writes new
     */
    private function rewriteMessage()
    {
        $this->buffer->putArrayOfValues(1, $this->height, array($this->message));
    }
}
