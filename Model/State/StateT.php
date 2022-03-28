<?php declare(strict_types=1);

namespace Model\State;
use Model\Dirty\DirtyT;

trait StateT {
    use DirtyT;

    public int $score = 0;
    public int $health = 1;

    public function addHealth(int $value): void {
        $this->health += $value;
        $this->setDirty();
    }

    public function addScore(int $value): void {
        $this->score += $value;
        $this->setDirty();
    }

    public function isDead(): bool { return $this->health <= 0; }
    public function getHealth(): int { return $this->health; }
    public function setHealth(int $value): void {
        $this->health = $value;
        $this->setDirty();
    }

    public function getScore(): int { return $this->score; }
}