<?php declare(strict_types=1);

namespace Model\Shape;
use Model\Board\IBoard;
use Model\Direction;
use Model\Entity\IEntity;
use Model\Log;
use Model\Player\Input;
use Model\Slot\ISlot;

// General purpose "entity manager"
abstract class ShapeBase implements IShape {
    protected IBoard $board;
    protected int $size;

    public final function tryInstantiate(IBoard $board, ?int $x = null, ?int $y = null, Direction $direction = null, int $size = 3): bool {
        Log::Message("Attempting to instantiate player shape.");
        $this->size = $size;
        $this->board = $board;
        if ($x === null || $y === null) {
            if (!($newSlot = $this->board->tryGetEmptySlot())) return false;
        } elseif (!($newSlot = $this->board->tryGetSlot($x, $y)) || !$newSlot->isEmpty()) return false;
        $x = $y = 0;
        $newSlot->getPosition(outX: $x, outY: $y);
        $this->instantiate($newSlot, $direction);
        Log::Message("Player shape instantiation successful.");
        return true;
    }

    protected abstract function instantiate(ISlot $slot, ?Direction $direction = null): void;
    public abstract function tryGetSlot(): ?ISlot;
    public abstract function getEntity(): IEntity;
    public function addSize(int $value): void { $this->size += $value; }
    public function getSize(): int { return $this->size; }
    public abstract function destroy(): void;
    public abstract function move(Input $input): void;
}
