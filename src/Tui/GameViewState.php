<?php

namespace App\Tui;

final readonly class GameViewState
{
    public function __construct(
        public GameFrame $frame,
        public string $message,
        public string $signature,
        public bool $gameOver = false,
    ) {
    }
}
