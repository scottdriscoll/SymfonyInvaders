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
    /**
     * @var string
     */
    private $current;
    
    /**
     * @var string
     */
    private $next;
    
    /**
     * @param string $initialValue
     */
    public function __construct($initialValue = ' ')
    {
        $this->current = null;
        $this->next = $initialValue;
    }
    
    /**
     * @return boolean
     */
    public function hasChanged()
    {
        return $this->current != $this->next;
    }
    
    public function nextFrame()
    {
        $this->current = $this->next;
    }

    /**
     * @param string $value
     */
    public function setNext($value)
    {
        $this->next = $value;
    }
    
    /**
     * @return string
     */
    public function getNext()
    {
        return $this->next;
    }
    
    /**
     * @return string
     */
    public function getCurrent()
    {
        return $this->current;
    }
    
}