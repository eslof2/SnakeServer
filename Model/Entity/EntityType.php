<?php declare(strict_types=1);

namespace Model\Entity;

enum EntityType: int {
    case FOOD = 1;
    case PLAYER = 2;
}