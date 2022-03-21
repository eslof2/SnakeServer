<?php declare(strict_types=1);

namespace Concrete\NormalMode;
use Concrete\Entity\FoodEntity;
use Concrete\View\DeltaView;
use Model\Entity\IEntity;
use Model\Game\GameBase;
use Model\Player\IPlayer;

class NormalGame extends GameBase {
    private FoodEntity $food;

    public function __construct(int $width, int $height, int $maxPlayers) {
        $board = new NormalBoard($width, $height);
        $this->view = new DeltaView();
        parent::__construct($board, $maxPlayers);
    }

    public function onStart(): void {
        $this->foodLoop();
    }

    protected function foodLoop(IEntity $consumable = null, IPlayer $player = null) {
        $this->food->detachFromSlot();
        if (!($newSlot = $this->board->tryGetEmptySlot())) throw new \Exception("Board is full of snake.");
        $food = $this->food = new FoodEntity();
        $food->setCollideCallback($this->foodLoop(...));
        $newSlot->add($this->food);
    }

    protected function getNewPlayer(int $fd, string $name): IPlayer {
        return new NormalPlayer($fd, $name);
    }
}