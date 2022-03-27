<?php declare(strict_types=1);

namespace Concrete\NormalMode;
use Model\Entity\IEntity;
use Model\InternalMisuseException;
use Model\Slot\SlotBase;

class NormalSlot extends SlotBase {
    // TODO: work in slotType so we can distinguish
    public function jsonSerialize(): ?IEntity {
        if (count($this->entities) > 1) throw new InternalMisuseException("Collisions are not consolidated at serialize-time.");
        if (count($this->entities) == 0) return null; //empty array takes up less space in json than null or false
        return end($this->entities); //although null vs just the entity without brackets... same [][] vs null
        // TODO: refactor this to null or entity rather than array
    }
}