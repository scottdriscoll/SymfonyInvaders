<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\InvadersBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use SD\ConsoleHelper\ScreenBuffer;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class RedrawEvent extends Event
{
    /**
     * @var ScreenBuffer
     */
    private $output;

    /**
     * @param ScreenBuffer $output
     */
    public function __construct(ScreenBuffer $output)
    {
        $this->output = $output;
    }

    /**
     * @return ScreenBuffer
     */
    public function getOutput()
    {
        return $this->output;
    }
}
