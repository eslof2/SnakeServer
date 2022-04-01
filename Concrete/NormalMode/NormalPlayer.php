<?php declare(strict_types=1);

namespace Concrete\NormalMode;
use Model\Direction;
use Model\Player\PlayerBase;

class NormalPlayer extends PlayerBase {
    public function __construct(public int $fd, public string $name) {
        $this->shape = new NormalPlayerShape($this->fd);
        parent::__construct($fd, $name);
    }
}