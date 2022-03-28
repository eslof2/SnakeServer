<?php declare(strict_types=1);

namespace Model\Board;
use Model\Game\IGame;
use Model\Slot\ISlot;
use SplFixedArray;

abstract class BoardBase implements IBoard {
    protected IGame $game;
    /** @var ISlot[][] */
    protected SplFixedArray $slots;

    public function __construct(
        protected int $width,
        protected int $height
    ) {
        $this->slots = new SplFixedArray($this->width);
        for ($x = 0; $x < $this->height; $x++) {
            $this->slots[$x] = new SplFixedArray($this->height);
            for ($y = 0; $y < $this->width; $y++) {
                $this->slots[$x][$y] = $this->getNewSlot($x, $y);
            }
        }
    }
    
    protected abstract function getNewSlot(int $x, int $y): ISlot;
    public function getGame(): IGame { return $this->game; }
    public function setGame(IGame $game): void { $this->game = $game; }
    public function getHeight(): int { return $this->height; }
    public function getSlot(int $x, int $y): ISlot { return $this->slots[$y][$x]; }
    /** @return ISlot[][] */
    public function getSlots(): \SplFixedArray { return $this->slots; }
    public function getWidth(): int { return $this->width; }
    public function tryGetEmptySlot(): ?ISlot {
        $emptySlots = [];
        foreach ($this->slots as $row) {
            foreach ($row as $slot) {
                if (!$slot->isEmpty()) continue;
                $emptySlots[] = $slot;
            }
        }
        if (count($emptySlots) == 0) return null;
        if (count($emptySlots) == 1) return $emptySlots[0];
        return $emptySlots[array_rand($emptySlots)];
    }

    public function tryGetSlot(int $x, int $y): ?ISlot {
        if ($y < 0 || $y >= $this->height ||
            $x < 0 || $x >= $this->width) return null;
        return $this->slots[$x][$y];
    }
}
