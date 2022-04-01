<?php

namespace Concrete\DevTest;
use Model\Entity\IEntity;
use Model\InternalMisuseException;
use Model\Slot\SlotBase;

class SingletonSlot extends SlotBase {
    // TODO: work in slotType so we can distinguish
    public function jsonSerialize(): ?IEntity {
        if (count($this->entities) > 1) throw new InternalMisuseException("Collisions are not consolidated at serialize-time.");
        $entityOrFalse = end($this->entities);
        return $entityOrFalse ?: null;
    }
}