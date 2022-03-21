<?php declare(strict_types=1);

namespace Concrete\Engine;

class Time {
    public float $deltaNs = 0;
    public int $tickCount = 0;
    public int $startTime;
    private int $lastHrtime;

    public function __construct() {
        $this->startTime = $this->lastHrtime = hrtime(true);
    }

    public function update(): void {
        $now = hrtime(true);
        $this->deltaNs = $now - $this->lastHrtime;
        $this->lastHrtime = $now;
        $this->tickCount++;
    }
}