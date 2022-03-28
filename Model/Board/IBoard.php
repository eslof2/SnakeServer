<?php declare(strict_types=1);

namespace Model\Board;
use Model\Slot\ISlot;
use SplFixedArray;

interface IBoard {
    public function getHeight(): int;
    public function getSlot(int $x, int $y): ISlot;
    /** @return ISlot[][] */
    public function getSlots(): SplFixedArray;
    public function getWidth(): int;
    public function tryGetEmptySlot(): ?ISlot;
    public function tryGetSlot(int $x, int $y): ?ISlot;
}