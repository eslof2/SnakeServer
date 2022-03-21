<?php declare(strict_types=1);

namespace Concrete\View;
use Model\Game\IGame;
use Model\InternalMisuseException;
use Model\View\ViewBase;

class DeltaView extends ViewBase {
    private int $fullStateTick = -1;
    private string $fullState;
    private int $deltaStateTick = -1;
    private ?string $deltaState;

    // managing full state vs delta state (aka only the changes from last) with cache
    public function serialize(IGame $game, int $serverTick, int $lastTick): string|null {
        if ($lastTick >= $serverTick) throw new InternalMisuseException("Giving a lastTick >= serverTick.");
        $toSerialize = new \stdClass();
        $isDelta = $serverTick - $lastTick == 1;
        if (!$isDelta) {
            if ($this->fullStateTick == $serverTick) return $this->fullState;
            $toSerialize->board = $game->getBoardSlots();
            $toSerialize->players = $game->getPlayers();
            $this->fullState = json_encode($game);
            $this->fullStateTick = $serverTick;
            return $this->fullState;
        } elseif ($this->deltaStateTick == $serverTick) return $this->deltaState;

        $toSerialize->board = [];
        $toSerialize->players = [];
        foreach ($game->getPlayers() as $player) {
            if (!$player->isDirty()) continue;
            $toSerialize->players[] = $player;
            $player->setDirty(false);
        }
        foreach ($game->getBoardSlots() as $y => $row) { //change to getBoardSlotArray (?)
            $deltaRow = [];
            foreach ($row as $x => $slot) {
                if (!$slot->isDirty()) continue;
                $deltaRow[$x] = $slot;
                $slot->setDirty(false);
            }
            if (count($deltaRow) == 0) continue;
            $toSerialize->board[$y] = $deltaRow;
        }

        if (count($toSerialize->board) + count($toSerialize->players) == 0) $this->deltaState = null;
        else $this->deltaState = json_encode($toSerialize);
        $this->deltaStateTick = $serverTick;
        return $this->deltaState;
    }
}