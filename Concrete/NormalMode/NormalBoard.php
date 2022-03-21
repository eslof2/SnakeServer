<?php declare(strict_types=1);

namespace Concrete\NormalMode;
use Model\Board\BoardBase;
use Model\Slot\ISlot;

class NormalBoard extends BoardBase {
    protected function getNewSlot(int $x, int $y): ISlot {
        return new NormalSlot($this, $x, $y);
    }
}