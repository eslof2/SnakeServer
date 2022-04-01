<?php declare(strict_types=1);

namespace Model;

enum AtomicState: int {
    case WAITING = 0;
    case STARTING = 2;
    case SHUTDOWN = 3;
    case ERROR = 4;
}