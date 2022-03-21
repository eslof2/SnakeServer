<?php declare(strict_types=1);

namespace Concrete\NormalMode;
use Model\Direction;
use Model\Player\PlayerBase;

class NormalPlayer extends PlayerBase {
    public function tryInstantiate(?int $x = null, ?int $y = null, ?Direction $direction = null): bool {
        $this->shape = new NormalPlayerShape($this->fd);
        return $this->shape->tryInstantiate($this->board);
    }
}