<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;


/**
 *
 * @author Richard Bunce <richard.bunce@opensoftdev.com>
 */
class ScreenBufferUnit
{
    private $current;
    
    private $next;
    
    public function __construct($initialValue = ' ')
    {
        $this->current = null;
        $this->next = $initialValue;
    }
    
    public function hasChanged()
    {
        return $this->current == $this->next;
    }
    
    public function nextFrame()
    {
        $this->current = $this->next;
    }

    public function setNext($value)
    {
        $this->next = $value;
    }
    
    public function getNext()
    {
        return $this->next;
    }
    
    public function getCurrent()
    {
        return $this->current;
    }
    
}