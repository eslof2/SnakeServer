<?php declare(strict_types=1);

namespace Concrete\NormalMode;
use Model\InternalMisuseException;
use Model\Slot\SlotBase;

class NormalSlot extends SlotBase {
    public function jsonSerialize(): array {
        if (count($this->entities) > 1) throw new InternalMisuseException("Collisions are not consolidated at serialize-time.");
        return $this->entities; //empty array takes up less space in json than null or false
        //return reset($this->entities) otherwise we'd use this
    }
}