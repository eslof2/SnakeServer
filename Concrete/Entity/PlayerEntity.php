<?php declare(strict_types=1);

namespace Concrete\Entity;
use Model\Entity\EntityBase;
use Model\Entity\EntityType;
use Model\Entity\PlayerType;
use Model\Player\IPlayer;

class PlayerEntity extends EntityBase {
    public EntityType $entityType = EntityType::PLAYER;

    public function __construct(public int $fd, public PlayerType $playerType) {}

    public function onCollide(IPlayer $player): void {
        $player->setHealth(0);
    }
}