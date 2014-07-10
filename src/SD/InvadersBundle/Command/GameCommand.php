<?php

namespace SD\InvadersBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SD\Game\Board;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class GameCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('invaders:launch');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Disable output for keypresses
        shell_exec('stty -icanon -echo');

        $gameBoard = new Board($output, 100, 40);
        $gameBoard->setMessage('Arrow keys to move, space to shoot.');
        $gameBoard->draw();

        while (1) {
            $key = '';
            if ($this->nonblockingRead($key)) {
                // Left = D, Right = C
/*                switch ($key) {
                    case 'D':
                        // Left arrow key
                        $output->writeln('<--');
                        break;
                    case 'C':
                        // Right arrow key
                        $output->writeln('-->');
                        break;
                    case ' ':
                        $output->writeln('space');
                        break;
                }
*/
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
