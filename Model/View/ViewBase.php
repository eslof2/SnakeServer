<?php declare(strict_types=1);

namespace Model\View;
use Model\Game\IGame;

abstract class ViewBase implements IView {
    public abstract function serialize(IGame $game, int $serverTick, int $lastTick): string|null;
}