<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\InvadersBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use SD\InvadersBundle\Helpers\OutputHelper;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class RedrawEvent extends Event
{
    /**
     * @var OutputHelper
     */
    private $output;

    /**
     * @param OutputHelper $output
     */
    public function __construct(OutputHelper $output)
    {
        $this->output = $output;
    }

    /**
     * @return OutputHelper
     */
    public function getOutput()
    {
        return $this->output;
    }
}
