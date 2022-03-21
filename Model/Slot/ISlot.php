<?php declare(strict_types=1);

namespace Model\Slot;
use Model\Board\IBoard;
use Model\Dirty\IDirty;
use Model\Entity\IEntity;
use Model\Position\IPosition;

interface ISlot extends IPosition, IDirty {
    public function add(IEntity $entity): void;
    public function isEmpty(): bool;
    public function remove(IEntity $entity): void;
    public function getBoard(): IBoard;
    /** @return IEntity[] */
    public function getEntities(): array;
}
