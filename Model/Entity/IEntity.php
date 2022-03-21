<?php declare(strict_types=1);

namespace Model\Entity;
use Model\Direction;
use Model\Dirty\IDirty;
use Model\Player\IPlayer;
use Model\Slot\ISlot;

interface IEntity extends IDirty {
    public function collide(IPlayer $player): void;
    public function getSlot(): ?ISlot;
    public function getDirection(): Direction;
    public function getEntityType(): EntityType;
    /** @param null|Closure(IEntity, IPlayer):void $onCollide */
    public function setCollideCallback(?\Closure $onCollide): void; //TODO: abstract callback pattern
    public function setDirection(Direction $direction): void;
    public function setSlot(?ISlot $slot): void;
    public function detachFromSlot(): ?ISlot;
}