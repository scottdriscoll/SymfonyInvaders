<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Game;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Event\AlienReachedEndEvent;
use App\Event\AlienDeadEvent;
use App\Event\PlayerHitEvent;
use App\Event\PlayerFireEvent;
use App\Event\GameOverEvent;
use App\Event\BossHitEvent;
use App\Event\BossDeadEvent;

class Board
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var int
     */
    private $shotsFired =0;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Boss $boss,
        private readonly Player $player,
        #[Autowire('%board_width%')]
        private readonly int $width,
        #[Autowire('%board_height%')]
        private readonly int $height,
    )
    {
    }

    public function initialize()
    {
        $this->initialized = true;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;

    }
  
    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }    

    #[AsEventListener]
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
    #[AsEventListener]
    public function bossDead(BossDeadEvent $event)
    {
        $this->setMessage("You win!! Total shots fired: " . $this->shotsFired . "\n");
        $this->eventDispatcher->dispatch(new GameOverEvent());
    }

    #[AsEventListener(priority: -255)]
    public function playerHit(PlayerHitEvent $event)
    {
        if ($this->player->getHealth() < 1) {
            $this->setMessage("You were killed!! Total shots fired: " . $this->shotsFired . "\n");
            $this->eventDispatcher->dispatch(new GameOverEvent());
        }
    }

    #[AsEventListener]
    public function alienReachedEnd(AlienReachedEndEvent $event)
    {
        $this->setMessage("An invader reached your home!! Total shots fired: " . $this->shotsFired . "\n");
        $this->eventDispatcher->dispatch(new GameOverEvent());
    }

    #[AsEventListener]
    public function playerFired(PlayerFireEvent $event)
    {
        $this->shotsFired++;
    }

    #[AsEventListener]
    public function bossHit(BossHitEvent $event)
    {
        $msg = "Boss health: " . $event->getHealth();
        $this->setMessage($msg);
    }
}
