<?php

namespace SD\Game;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("game.keyboard")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class Keyboard
{
    /**
     * @var string
     */
    const RIGHT_ARROW = 'C';

    /**
     * @var string
     */
    const LEFT_ARROW = 'D';

    /**
     * @var string
     */
    const FIRE_KEY = ' ';

    public function listenAndFireEvents()
    {
        while (1) {
            $key = '';
            if ($this->nonblockingRead($key)) {
                switch ($key) {
                    case self::LEFT_ARROW:
                        break;

                    case self::RIGHT_ARROW:
                        break;

                    case self::FIRE_KEY:
                        break;
                }
            }

            usleep(8000);
        }
    }

    /**
     * Reads from a stream without waiting for a \n character.
     *
     * @param string $data
     *
     * @return bool
     */
    private function nonblockingRead(&$data)
    {
        $read = [STDIN];
        $write = [];
        $except = [];
        $result = stream_select($read, $write, $except, 0);

        if ($result === false || $result === 0) {
            return false;
        }

        $data = stream_get_line(STDIN, 1);

        return true;
    }
}
