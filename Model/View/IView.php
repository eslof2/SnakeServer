<?php declare(strict_types=1);

namespace Model\View;
use Model\Game\IGame;

interface IView {
    public function serialize(IGame $game, int $serverTick, int $lastTick): string|null;
}