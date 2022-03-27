<?php declare(strict_types=1);

namespace Concrete\NormalMode;
use Model\Entity\IEntity;
use Model\InternalMisuseException;
use Model\Slot\SlotBase;

class NormalSlot extends SlotBase {
    // TODO: work in slotType so we can distinguish
    // TODO: rework dependency so I can more esily make a new slot type and give it to the board and give board to the game
    public function jsonSerialize(): ?IEntity {
        if (count($this->entities) > 1) throw new InternalMisuseException("Collisions are not consolidated at serialize-time.");
        $entityOrFalse = end($this->entities);
        return $entityOrFalse ?: null;
    }
}