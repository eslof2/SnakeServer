<?php declare(strict_types=1);

namespace Server;

enum PlayerRequest: int {
    case JOIN = 1;
    case INPUT = 2;
}