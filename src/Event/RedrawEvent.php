<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\Tui\GameFrame;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class RedrawEvent extends Event
{
    /**
     * @var GameFrame
     */
    private $output;

    /**
     * @param GameFrame $output
     */
    public function __construct(GameFrame $output)
    {
        $this->output = $output;
    }

    /**
     * @return GameFrame
     */
    public function getOutput()
    {
        return $this->output;
    }
}
