<?php

namespace SD\Game;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class Board
{
    /**
     * @var string
     */
    public $message;

    /**
     * @var int
     */
    private $width = 200;

    /**
     * @var int
     */
    private $height = 100;

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
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function draw()
    {
        $this->clear();

        $output = str_repeat("\n", $this->height);




        $this->initialized = true;
    }

    public function setMessage($message)
    {
        $this->message = $message;

        if ($this->initialized) {
            $this->rewriteMessage();
        }
    }

    private function clear()
    {
        // Reset cursor
        $this->output->write("\x0D");

    }

    private function rewriteMessage()
    {

    }
}
