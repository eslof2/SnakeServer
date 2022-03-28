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
        if (array_key_exists($key, $this->entities)) throw new InternalMisuseException("Adding entity already in slot.");
        $this->entities[$key] = $entity;
        $entity->setSlot($this);
        $this->setDirty();
    }

    public function remove(IEntity $entity): void {
        $key = spl_object_id($entity);
        if (!array_key_exists($key, $this->entities)) throw new InternalMisuseException("Removing entity not in slot.");
        $this->entities[$key]->setSlot(null);
        unset($this->entities[$key]);
        $this->setDirty();
    }

    public function isEmpty(): bool { return count($this->entities) == 0; }
    public function getBoard(): IBoard { return $this->board; }
    public function getEntities(): array { return $this->entities; }
    /** @return IEntity[] */
    public function jsonSerialize(): mixed { return array_values($this->entities); }
    public function isDirty(): bool {
        if ($this->isDirty) return true;
        foreach ($this->entities as $entity) {
            if ($entity->isDirty()) return true;
        }
        return false;
    }

    public function setDirty($isDirty = true): void {
        $this->isDirty = $isDirty;
        if ($isDirty === false) {
            foreach ($this->entities as $entity) {
                $entity->setDirty(false);
            }
        }
    }
}