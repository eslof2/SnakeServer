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

    // todo: keep track of all entities instead of iterating slots
    // todo: send entity with x and y, more optimal over wire
    //  and full state is just all entities regardless of dirty + gridSize (and max player)
    // managing full state vs delta state (aka only the changes from last) with cache
    public function serialize(IGame $game, int $serverTick, int $lastTick): string|null {
        if ($lastTick >= $serverTick) throw new InternalMisuseException("Giving a lastTick >= serverTick.");
        $isDelta = $serverTick - $lastTick == 1;
        if (!$isDelta) {
            $toSerialize = new \stdClass();
            if ($this->fullStateTick == $serverTick) return $this->fullState;
            $board = $game->getBoard()->getSlots();
            $players = $game->getPlayers();
            if (count($board) == 0) throw new InternalMisuseException("Serializing an empty full state board, only possible if board size is 0x0.");
            $toSerialize->board = $board;
            if (count($players) > 0) $toSerialize->players = $players;
            $this->fullState = json_encode($toSerialize);
            $this->fullStateTick = $serverTick;
            return $this->fullState;
        } elseif ($this->deltaStateTick == $serverTick) return $this->deltaState;

        $board = [];
        $players = [];
        foreach ($game->getPlayers() as $player) {
            if (!$player->isDirty()) continue;
            $players[] = $player;
            $player->setDirty(false);
        }
        foreach ($game->getBoard()->getSlots() as $y => $row) { //change to getBoardSlotArray (?)
            $deltaRow = [];
            foreach ($row as $x => $slot) {
                if (!$slot->isDirty()) continue;
                $deltaRow[$x] = $slot;
                $slot->setDirty(false);
            }
            if (count($deltaRow) == 0) continue;
            $board[$y] = $deltaRow;
        }

        if (count($board) + count($players) == 0) $this->deltaState = null;
        else {
            $toSerialize = new \stdClass();
            if (count($board) > 0) $toSerialize->board = $board;
            if (count($players) > 0) $toSerialize->players = $players;
            $this->deltaState = json_encode($toSerialize);
        }
        $this->deltaStateTick = $serverTick;
        return $this->deltaState;
    }
}