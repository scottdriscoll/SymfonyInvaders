<?php

namespace SD\Game;

use Symfony\Component\Console\Output\OutputInterface;
use JMS\DiExtraBundle\Annotation as DI;

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

        // Reset cursor
        $this->output->write("\x0D");

        $output = str_repeat("\n", $this->height);

        $lines = explode("\n", $output);

        // move back to the beginning of the progress bar before redrawing it
        $this->output->write("\x0D");
        $this->output->write(sprintf("\033[%dA", $this->height));
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

    public function setMessage($message)
    {
        $this->message = $message;

        if ($this->initialized) {
            $this->rewriteMessage();
        }
    }

    private function rewriteMessage()
    {
//        $this->output->write(sprintf("\033[%dA", 20));
        $this->output->write(sprintf("\033[%dB", $this->height + 1));
        $this->output->write("\x0D");
        // Erase old message
        $this->output->write(str_pad('', $this->width, ' '));
        // Write new message
        $this->output->write("\x0D");
        $this->output->write($this->message);
    }
}
