<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\InvadersBundle\Helpers;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class OutputHelper
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param string $msg
     */
    public function write($msg)
    {
        $this->output->write($msg);
    }

    /**
     * @param string $msg
     */
    public function writeln($msg)
    {
        $this->output->writeln($msg);
    }

    /**
     * @param int $lines
     */
    public function moveCursorDown($lines)
    {
        $this->output->write(sprintf("\033[%dB", $lines));
    }

    /**
     * @param int $lines
     */
    public function moveCursorUp($lines)
    {
        $this->output->write(sprintf("\033[%dA", $lines));
    }

    /**
     * @param int $spaces
     */
    public function moveCursorRight($spaces)
    {
        $this->output->write(sprintf("\033[%dC", $spaces));
    }

    public function moveCursorFullLeft()
    {
        $this->output->write("\x0D");
    }

    public function disableKeyboardOutput()
    {
        shell_exec('stty -icanon -echo');
    }

    public function hideCursor()
    {
        \Hoa\Console\Cursor::hide();
    }

    static public function showCursor()
    {
        shell_exec('stty icanon echo');
        \Hoa\Console\Cursor::show();
    }
}

register_shutdown_function('\SD\InvadersBundle\Helpers\OutputHelper::showCursor');
