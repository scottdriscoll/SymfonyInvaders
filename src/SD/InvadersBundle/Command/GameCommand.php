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

        $gameBoard = new Board($output);
        $gameBoard->draw();

        $boardHeight = 20;

        // moves up
        $output->write(sprintf("\033[%dA", 20));
        $output->writeln("|----------------------------------|");
        for ($i = 0; $i < $boardHeight - 2; $i++) {
            $output->writeln("|                                  |");
        }
        $output->writeln("|__________________________________|");

        // Moves down
//        $output->write(sprintf("\033[%dB", 80));
        $output->write("\nArrow keys to move, space to shoot.");

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

    private function overwrite(OutputInterface $output, $message)
    {
        $lines = explode("\n", $message);

        // move back to the beginning of the progress bar before redrawing it
        $output->write("\x0D");
        $output->write(sprintf("\033[%dA", 80));
        $output->write(implode("\n", $lines));

        $this->lastMessagesLength = 0;
        foreach ($lines as $line) {
            $len = strlen($line);
            if ($len > $this->lastMessagesLength) {
                $this->lastMessagesLength = $len;
            }
        }
    }
}
