<?php

namespace App\Tui;

final class GameFrame
{
    /**
     * @var array<int, array<int, array{value: string, color: ?string}>>
     */
    private array $cells = [];

    public function __construct(
        public readonly int $width,
        public readonly int $height,
    ) {
    }

    public function putNextValue(int $x, int $y, string $value, ?string $color = null): void
    {
        if ($x < 0 || $x >= $this->width || $y < 0 || $y >= $this->height) {
            return;
        }

        $this->cells[$y][$x] = [
            'value' => $value[0] ?? ' ',
            'color' => $color,
        ];
    }

    /**
     * @param string[] $values
     */
    public function putArrayOfValues(int $x, int $y, array $values, ?string $color = null): void
    {
        foreach ($values as $rowOffset => $row) {
            $chars = str_split($row);
            foreach ($chars as $columnOffset => $char) {
                $this->putNextValue($x + $columnOffset, $y + $rowOffset, $char, $color);
            }
        }
    }

    /**
     * @return array<int, array<int, array{value: string, color: ?string}>>
     */
    public function getCells(): array
    {
        return $this->cells;
    }

    public function signature(): string
    {
        $parts = [];

        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $cell = $this->cells[$y][$x] ?? ['value' => ' ', 'color' => null];
                $parts[] = $cell['value'].':'.($cell['color'] ?? '');
            }
        }

        return hash('xxh3', implode('|', $parts));
    }
}
