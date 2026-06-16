<?php

namespace App\Tui;

use App\Event\RedrawEvent;
use App\Game\Board;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class GameViewStateFactory
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private Board $board,
        #[Autowire('%board_width%')]
        private int $boardWidth,
        #[Autowire('%board_height%')]
        private int $boardHeight,
    ) {
    }

    public function create(bool $gameOver = false): GameViewState
    {
        $frame = new GameFrame($this->boardWidth, $this->boardHeight);
        $this->drawBorder($frame);

        $this->eventDispatcher->dispatch(new RedrawEvent($frame));

        $message = $this->board->getMessage() ?? '';

        return new GameViewState(
            $frame,
            $message,
            hash('xxh3', $frame->signature().'|'.$message.'|'.($gameOver ? '1' : '0')),
            $gameOver,
        );
    }

    private function drawBorder(GameFrame $frame): void
    {
        for ($x = 0; $x < $frame->width; $x++) {
            $frame->putNextValue($x, 0, '-', 'yellow');
            $frame->putNextValue($x, $frame->height - 1, '-', 'yellow');
        }

        for ($y = 0; $y < $frame->height; $y++) {
            $frame->putNextValue(0, $y, '|', 'yellow');
            $frame->putNextValue($frame->width - 1, $y, '|', 'yellow');
        }
    }
}
