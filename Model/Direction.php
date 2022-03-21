<?php declare(strict_types=1);

namespace Model;
use Model\Player\Input;

enum Direction {
    case DOWN;
    case LEFT;
    case RIGHT;
    case UP;

    public function rotate(Input $input): Direction {
        return match ($input) {
            Input::LEFT => match ($this) {
                self::DOWN => self::RIGHT,
                self::LEFT => self::DOWN,
                self::RIGHT => self::UP,
                self::UP => self::LEFT,
            },
            Input::RIGHT => match ($this) {
                self::DOWN => self::LEFT,
                self::LEFT => self::UP,
                self::RIGHT => self::DOWN,
                self::UP => self::RIGHT,
            },
            Input::NONE => $this
        };
    }

    public function move(int &$refX, int &$refY): void {
        match ($this) {
            Direction::DOWN => $refY--,
            Direction::LEFT => $refX--,
            Direction::RIGHT => $refX++,
            Direction::UP => $refY++
        };
    }
}