<?php declare(strict_types=1);

namespace Model\Dirty;

interface IDirty {
    public function isDirty(): bool;
    public function setDirty(bool $isDirty = true): void;
}