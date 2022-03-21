<?php declare(strict_types=1);

namespace Concrete\Entity;
use Model\Entity\EntityBase;
use Model\Entity\EntityType;
use Model\Player\IPlayer;

class FoodEntity extends EntityBase {
    public EntityType $entityType = EntityType::FOOD;
    public int $scoreValue = 1;
    public int $sizeValue = 1;

    public function onCollide(IPlayer $player): void {
        $player->addScore($this->scoreValue);
        $player->getShape()->addSize($this->sizeValue);
    }
}