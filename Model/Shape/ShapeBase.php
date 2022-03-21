<?php declare(strict_types=1);

namespace Model\Shape;
use Model\Board\IBoard;
use Model\Direction;
use Model\Player\Input;
use Model\Slot\ISlot;

// General purpose "entity manager"
abstract class ShapeBase implements IShape {
    protected IBoard $board;
    protected int $size;

    public final function tryInstantiate(IBoard $board, ?int $x = null, ?int $y = null, Direction $direction = null): bool {
        $this->board = $board;
        if (is_null($x) || is_null($y)) {
            if (!($newSlot = $this->board->tryGetEmptySlot())) return false;
        } elseif (!($newSlot = $this->board->tryGetSlot($x, $y)) || !$newSlot->isEmpty()) return false;
        $x = $y = 0;
        $newSlot->getPosition(outX: $x, outY: $y);
        $this->instantiate($newSlot, $direction);
        return true;
    }
    public abstract function tryGetSlot(): ?ISlot;
    protected abstract function instantiate(ISlot $slot, ?Direction $direction = null): void;
    public function addSize(int $value): void { $this->size += $value; }
    public function getSize(): int { return $this->size; }
    public abstract function destroy(): void;
    public abstract function move(Input $input): void;
}
