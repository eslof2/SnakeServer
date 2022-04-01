<?php declare(strict_types=1);

namespace Model\Game;

enum GameState {
    case PLAYING;
    case FINISHED;
}