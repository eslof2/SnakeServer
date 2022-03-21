<?php declare(strict_types=1);

namespace Model\Position;

interface IPosition {
    public function getPosition(int &$outX, int &$outY): void;
    public function setPosition(int $x, int $y): void;
}