<?php declare(strict_types=1);

namespace Model\Position;
use Model\InternalMisuseException;

trait PositionT {
    protected int $x;
    protected int $y;

    public function getPosition(int &$outX, int &$outY): void {
        if (!isset($this->x) || !isset($this->y)) throw new InternalMisuseException("Getting position before it's set.");
        $outX = $this->x;
        $outY = $this->y;
    }

    public function setPosition(int $x, int $y): void {
        $this->x = $x;
        $this->y = $y;
    }
}