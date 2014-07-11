<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\InvadersBundle\Helpers\OutputHelper;
use SD\InvadersBundle\Events;
use SD\InvadersBundle\Event\PlayerInitializedEvent;
use SD\InvadersBundle\Event\PlayerMovedEvent;
use SD\InvadersBundle\Event\PlayerProjectilesUpdatedEvent;
use SD\InvadersBundle\Event\RedrawEvent;

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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

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
     * @param OutputHelper $output
     * @param int $width
     * @param int $height
     */
    public function draw(OutputHelper $output, $width, $height)
    {
        $this->output = $output;
        $this->width = $width;
        $this->height = $height;

        $lines = explode("\n", str_repeat("\n", $this->height));

        // move back to the beginning of the progress bar before redrawing it
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
     * @DI\Observe(Events::PLAYER_INITIALIZED, priority = 0)
     *
     * @param PlayerInitializedEvent $event
     */
    public function addPlayer(PlayerInitializedEvent $event)
    {
        $this->drawPlayer($event->getCurrentXPosition());
    }

    /**
     * @DI\Observe(Events::PLAYER_MOVED, priority = 0)
     *
     * @param PlayerMovedEvent $event
     */
    public function updatePlayer(PlayerMovedEvent $event)
    {
        $this->drawPlayer($event->getCurrentXPosition());
    }

    /**
     * @DI\Observe(Events::PLAYER_PROJECTILES_UPDATED, priority = 0)
     *
     * @param PlayerProjectilesUpdatedEvent $event
     */
    public function playerProjectilesChanged(PlayerProjectilesUpdatedEvent $event)
    {
        $this->redrawBoard();
    }

    public function redrawBoard()
    {
        // Reset cursor to a known position
        $this->output->moveCursorDown($this->height);
        $this->output->moveCursorFullLeft();
        $this->output->moveCursorUp($this->height);

        $top = '<fg=yellow>|' . str_pad('', $this->width - 2, '-') . '|</fg=yellow>';
        $middle = '<fg=yellow>|' . str_pad('', $this->width - 2, ' ') . '|</fg=yellow>';

        $this->output->writeln($top);
        for ($i = 0; $i < $this->height - 3; $i++) {
            $this->output->writeln($middle);
        }

        $this->output->moveCursorDown(5);

        $this->eventDispatcher->dispatch(Events::BOARD_REDRAW, new RedrawEvent($this->output));
    }

    /**
     * @param int $xPosition
     */
    private function drawPlayer($xPosition)
    {
        // Reset cursor to a known position
        $this->output->moveCursorDown($this->height + 1);
        $this->output->moveCursorFullLeft();

        // Move to proper location
        $this->output->moveCursorUp(2);
        $player = '|' . str_pad('', $xPosition, ' ') . '^' . str_pad('', $this->width - $xPosition - 3, ' ');
        $this->output->write($player);

        // Move cursor out of the way
        $this->output->moveCursorDown(2);
        $this->output->moveCursorFullLeft();
    }

    /**
     * Erases old message and writes new
     */
    private function rewriteMessage()
    {
        $this->output->moveCursorDown($this->height + 1);
        $this->output->moveCursorFullLeft();
        // Erase old message
        $this->output->write(str_pad('', $this->width, ' '));
        // Write new message
        $this->output->moveCursorFullLeft();
        $this->output->write($this->message);
    }
}
