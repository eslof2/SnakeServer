<?php declare(strict_types=1);

namespace Model\State;

use Model\Dirty\IDirty;

interface IState extends IDirty {
    public function addHealth(int $value): void;
    public function addScore(int $value): void;
    public function setHealth(int $value): void;
    public function isDead(): bool;
    public function getHealth(): int;
    public function getScore(): int;
}