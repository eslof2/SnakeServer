<?php declare(strict_types=1);

namespace Model\Dirty;

// dirty means someone touched it since we last saw it
// in this case it's for network serialization, to only send what's been changed
trait DirtyT {
    private bool $isDirty = true;
    public function isDirty(): bool { return $this->isDirty; }
    public function setDirty(bool $isDirty = true): void { $this->isDirty = $isDirty; }
}