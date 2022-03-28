<?php declare(strict_types=1);

namespace Model\Entity;
use Closure;
use Model\Direction;
use Model\Dirty\DirtyT;
use Model\Player\IPlayer;
use Model\Slot\ISlot;

abstract class EntityBase implements IEntity {
    use DirtyT;

    public EntityType $entityType;
    public Direction $direction = Direction::DOWN;
    protected ?ISlot $slot = null;
    /** @var null|Closure(IEntity, IPlayer):void */
    private ?Closure $collideCallback = null;

    public final function collide(IPlayer $player): void {
        $this->onCollide($player);
        //I think there's a decent way to abstract this callback pattern with traits and "as"
        if ($this->collideCallback !== null) ($this->collideCallback)($this, $player);
    }

    // we give inherited types an entry point for custom code
    public abstract function onCollide(IPlayer $player): void;
    public function detachFromSlot(): void { $this->slot?->remove($this); }
    public function getDirection(): Direction { return $this->direction; }
    public function setDirection(Direction $direction): void {
        $this->direction = $direction;
        $this->setDirty();
    }

    public function getEntityType(): EntityType { return $this->entityType; }
    public function tryGetSlot(): ?ISlot { return $this->slot; }
    public function setSlot(?ISlot $slot): void { $this->slot = $slot; }
    /** @param null|Closure(IEntity, IPlayer):void $onCollide */
    public function setCollideCallback(?Closure $onCollide): void { $this->collideCallback = $onCollide; }
}