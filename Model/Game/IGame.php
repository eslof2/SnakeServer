<?php declare(strict_types=1);

namespace Model\Game;
use Concrete\Engine\Time;
use Model\Board\IBoard;
use Model\Player\IPlayer;
use Model\Slot\ISlot;

interface IGame {
    public function getBoard(): IBoard;
    public function getPlayer(int $fd): IPlayer;
    /** @return IPlayer[] */
    public function getPlayers(): array;
    public function getTickRateNs(): int;
    public function getTimeScale(): float;
    public function onDisconnect(int $fd): void;
    public function process(): void;
    public function setTimeScale(float $timeScale): void;
    public function start(Time $time): void;
    public function tryAddPlayer(IPlayer $player): bool;
    public function tryGetPlayer(int $fd): ?IPlayer;
}