<?php declare(strict_types=1);

namespace Model\Slot;
use JsonSerializable;
use Model\Board\IBoard;
use Model\Dirty\DirtyT;
use Model\Entity\IEntity;
use Model\InternalMisuseException;
use Model\Position\PositionT;

abstract class SlotBase implements ISlot, JsonSerializable {
    use DirtyT;
    use PositionT;

    /** @var IEntity[] $entities */
    protected array $entities = [];

    public function __construct(protected IBoard $board, protected int $x, protected int $y) {}

    public function add(IEntity $entity): void {
        $key = spl_object_id($entity);
        if (isset($this->entities[$key])) throw new InternalMisuseException("Adding entity already in slot.");
        $this->entities[$key] = $entity;
        $entity->setSlot($this);
        $this->setDirty();
    }

    public function remove(IEntity $entity): void {
        $key = spl_object_id($entity);
        if (!isset($this->entities[$key])) throw new InternalMisuseException("Removing entity not in slot.");
        unset($this->entities[$key]);
        $this->setDirty();
    }

    public function isEmpty(): bool { return count($this->entities) == 0; }
    public function getBoard(): IBoard { return $this->board; }
    public function getEntities(): array { return $this->entities; }
    public function jsonSerialize(): array { return $this->entities; }
    public function isDirty(): bool {
        if ($this->isDirty) return true;
        foreach ($this->entities as $entity) {
            if ($entity->isDirty()) return true;
        }
        return false;
    }
}