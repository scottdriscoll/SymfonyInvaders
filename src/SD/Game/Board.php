<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\Console\Output\OutputInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\InvadersBundle\Events;
use SD\InvadersBundle\Event\PlayerInitializedEvent;
use SD\InvadersBundle\Event\PlayerMovedEvent;

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
     * @var OutputInterface
     */
    private $output;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @param OutputInterface $output
     * @param int $width
     * @param int $height
     */
    public function draw(OutputInterface $output, $width, $height)
    {
        $this->output = $output;
        $this->width = $width;
        $this->height = $height;

        $lines = explode("\n", str_repeat("\n", $this->height));

        // move back to the beginning of the progress bar before redrawing it
        $this->moveCursorFullLeft();
        $this->moveCursorUp($this->height);
        $this->output->write(implode("\n", $lines));

        $top = '|' . str_pad('', $this->width - 2, '-') . '|';
        $middle = '|' . str_pad('', $this->width - 2, ' ') . '|';

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
     * @param int $xPosition
     */
    private function drawPlayer($xPosition)
    {
        // Reset cursor to a known position
        $this->moveCursorDown($this->height + 1);
        $this->moveCursorFullLeft();

        // Move to proper location
        $this->moveCursorUp(2);
        $player = '|' . str_pad('', $xPosition, ' ') . '^' . str_pad('', $this->width - $xPosition - 3, ' ');
        $this->output->write($player);

        // Move cursor out of the way
        $this->moveCursorDown(2);
        $this->moveCursorFullLeft();
    }

    /**
     * @param int $lines
     */
    private function moveCursorDown($lines)
    {
        $this->output->write(sprintf("\033[%dB", $lines));
    }

    /**
     * @param int $lines
     */
    private function moveCursorUp($lines)
    {
        $this->output->write(sprintf("\033[%dA", $lines));
    }

    private function moveCursorFullLeft()
    {
        $this->output->write("\x0D");
    }

    /**
     * Erases old message and writes new
     */
    private function rewriteMessage()
    {
        $this->moveCursorDown($this->height + 1);
        $this->moveCursorFullLeft();
        // Erase old message
        $this->output->write(str_pad('', $this->width, ' '));
        // Write new message
        $this->moveCursorFullLeft();
        $this->output->write($this->message);
    }
}
