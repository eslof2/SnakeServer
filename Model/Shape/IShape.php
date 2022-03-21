<?php declare(strict_types=1);

namespace Model\Shape;
use Model\Board\IBoard;
use Model\Direction;
use Model\Player\Input;
use Model\Slot\ISlot;

interface IShape {
    public function destroy(): void;
    public function tryInstantiate(IBoard $board, ?int $x = null, ?int $y = null, ?Direction $direction = null): bool;
    public function tryGetSlot(): ?ISlot;
    public function move(Input $input): void;
    public function getSize(): int;
    public function addSize(int $value): void;
}