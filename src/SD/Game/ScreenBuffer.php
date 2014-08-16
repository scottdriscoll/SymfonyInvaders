<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\InvadersBundle\Helpers\OutputHelper;
use SD\InvadersBundle\Events;

/**
 * @DI\Service("game.screen_buffer")
 * 
 * @author Richard Bunce <richard.bunce@opensoftdev.com>
 */
class ScreenBuffer
{
    /**
     * @var array
     */
    private $screen;
    
    /**
     * @var int
     */
    private $height;
    
    /**
     * @var int
     */
    private $width;

    /**
     * @param int $width
     * @param int $height
     */
    public function intialize($width = 100, $height = 30)
    {
        $this->screen = array();
        $this->height = $height;
        $this->width = $width;
        for ($i = 0; $i < $height; $i++) {
            for ($j = 0; $j < $width; $j++) {
                $this->screen[$i][$j] = new ScreenBufferUnit();
            }
        }
    }
    
    /** 
     * @param string $value
     */
    public function clearScreen($value = ' ')
    {
        foreach ($this->screen as $y => $row) {
            foreach ($row as $x => $unit) {
                $unit->setNext($value);
            }
        }        
    }
  
    public function nextFrame()
    {
        foreach ($this->screen as $y => $row) {
            foreach ($row as $x => $unit) {
                $unit->nextFrame();
            }
        }            
    }
    
    /**
     * @param int $x
     * @param int $y
     * @param string $value
     * @return boolean
     */
    public function putNextValue($x, $y, $value)
    {
        if ($x < 0 || $x >= $this->width || $y < 0 || $y >= $this->height) {
            return false;
        }
        $this->screen[$y][$x]->setNext($value);
    }
    
    /** 
     * @param OutputHelper $output
     */
    public function paintChanges(OutputHelper $output)
    {
        foreach ($this->screen as $y => $row) {
            foreach ($row as $x => $unit) {
                if ($unit->hasChanged()) {
                    //paint $unit->getNext()
                    $output->moveCursorUp(100);
                    $output->moveCursorFullLeft();
                    if ($y > 0) {
                        $output->moveCursorDown($y);
                    }
                    if ($x > 0) {
                        $output->moveCursorRight($x);
                    }
                    $output->write($unit->getNext());
            
                }
            }
        }
    }
}
