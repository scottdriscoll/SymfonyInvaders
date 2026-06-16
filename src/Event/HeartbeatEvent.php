<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class HeartbeatEvent extends Event
{
    /**
     * @var int
     */
    private $timestamp;

    /**
     * @param int $timestamp
     */
    public function __construct($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
