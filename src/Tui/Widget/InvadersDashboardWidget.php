<?php

namespace App\Tui\Widget;

use App\Tui\GameViewState;
use Symfony\Component\Tui\Render\RenderContext;
use Symfony\Component\Tui\Widget\AbstractWidget;

final class InvadersDashboardWidget extends AbstractWidget
{
    private ?GameViewState $state = null;
    private ?string $lastSignature = null;

    public function setState(GameViewState $state): bool
    {
        if ($this->lastSignature === $state->signature) {
            return false;
        }

        $this->state = $state;
        $this->lastSignature = $state->signature;
        $this->invalidate();

        return true;
    }

    /**
     * @return string[]
     */
    public function render(RenderContext $context): array
    {
        $columns = max(1, $context->getColumns());

        if (null === $this->state) {
            return [$this->plainLine('Loading Symfony Invaders...', $columns)];
        }

        $lines = [
            $columns >= 16 ? "\033[1mSymfony Invaders\033[0m" : $this->plainLine('Symfony Invaders', $columns),
            $this->plainLine('Arrows move, space shoots, q quits', $columns),
            '',
        ];

        $cells = $this->state->frame->getCells();
        $boardColumns = min($this->state->frame->width, $columns);

        for ($y = 0; $y < $this->state->frame->height; $y++) {
            $line = '';
            for ($x = 0; $x < $boardColumns; $x++) {
                $cell = $cells[$y][$x] ?? ['value' => ' ', 'color' => null];
                $line .= $this->cell($cell['value'], $cell['color']);
            }
            $lines[] = $line;
        }

        $lines[] = $this->plainLine($this->state->message, $columns);

        if ($this->state->gameOver) {
            $lines[] = '';
            $lines[] = $columns >= 9 ? "\033[1;31mGame over\033[0m" : $this->plainLine('Game over', $columns);
        }

        return $lines;
    }

    private function cell(string $value, ?string $color): string
    {
        $code = match ($color) {
            'red' => 31,
            'green' => 32,
            'yellow' => 33,
            'blue' => 34,
            'magenta' => 35,
            'cyan' => 36,
            default => null,
        };

        if (null === $code) {
            return $value;
        }

        return "\033[{$code}m{$value}\033[0m";
    }

    private function plainLine(string $value, int $columns): string
    {
        return mb_substr($value, 0, $columns);
    }
}
