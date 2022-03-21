<?php declare(strict_types=1);

namespace Model;

enum GameState: int {
    case WAITING = 0;
    case RUNNING = 1;
    case STARTING = 2;
    case SHUTDOWN = 3;
    case ERROR = 4;
}