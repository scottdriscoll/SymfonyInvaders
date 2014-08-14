<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\InvadersBundle\Helpers\OutputHelper;
use SD\InvadersBundle\Events;
use SD\InvadersBundle\Event\PlayerProjectilesUpdatedEvent;
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
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "boss" = @DI\Inject("game.boss"),
     *     "player" = @DI\Inject("game.player"),
     *     "width" = @DI\Inject("%board_width%"),
     *     "height" = @DI\Inject("%board_height%")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param Boss $boss
     * @param Player $player
     * @param int $width
     * @param int $height
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, Boss $boss, Player $player, $width, $height)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->boss = $boss;
        $this->player = $player;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @param OutputHelper $output
     */
    public function draw(OutputHelper $output)
    {
        $this->output = $output;

        $lines = explode("\n", str_repeat("\n", $this->height));

        // move back to the beginning of the progress bar before redrawing it
        $this->output->clear();
        $this->output->moveCursorFullLeft();
        $this->output->moveCursorUp($this->height);
        $this->output->write(implode("\n", $lines));

        $top = '<fg=yellow>|' . str_pad('', $this->width - 2, '-') . '|</fg=yellow>';
        $middle = '<fg=yellow>|' . str_pad('', $this->width - 2, ' ') . '|</fg=yellow>';

        $this->output->writeln($top);
        for ($i = 0; $i < $this->height - 2; $i++) {
            $this->output->writeln($middle);
        }
        $this->output->writeln($top);
        $this->output->dump();

        $this->initialized = true;

        if (!empty($this->message)) {
            $this->rewriteMessage($this->message);
        }
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
     * @DI\Observe(Events::ALIEN_DEAD, priority = 0)
     *
     * @param AlienDeadEvent $event
     */
    public function alienHit(AlienDeadEvent $event)
    {
        if ($event->getAliveAliens() == 0) {
            if (!$this->boss->isSpawned()) {
                $this->boss->spawnBoss($this->width, $this->height);
            }
        } elseif (!$this->boss->isSpawned()) {
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
        $this->setMessage("\n\nYou win!! Total shots fired: " . $this->shotsFired . "\n");
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
            $this->setMessage("\n\nYou were killed!! Total shots fired: " . $this->shotsFired . "\n");
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
        $this->setMessage("\n\nAn invader reached your home!! Total shots fired: " . $this->shotsFired . "\n");
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

        // Reset cursor to a known position
        $this->output->moveCursorDown($this->height);
        $this->output->moveCursorFullLeft();
        $this->output->moveCursorUp($this->height);

        $top = '<fg=yellow>|' . str_pad('', $this->width - 2, '-') . '|</fg=yellow>';
        $middle = '<fg=yellow>|' . str_pad('', $this->width - 2, ' ') . '|</fg=yellow>';

        $this->output->writeln($top);
        for ($i = 0; $i < $this->height - 2; $i++) {
            $this->output->writeln($middle);
        }

        $this->output->moveCursorDown(5);

        $this->eventDispatcher->dispatch(Events::BOARD_REDRAW, new RedrawEvent($this->output));

        $this->output->dump();
    }

    /**
     * Erases old message and writes new
     */
    private function rewriteMessage()
    {
        $this->output->clear();
        $this->output->moveCursorDown($this->height + 1);
        $this->output->moveCursorFullLeft();
        // Erase old message
        $this->output->write(str_pad('', $this->width, ' '));
        // Write new message
        $this->output->moveCursorFullLeft();
        $this->output->write($this->message);
        $this->output->dump();
    }
}
