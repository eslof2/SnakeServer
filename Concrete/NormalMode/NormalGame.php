<?php declare(strict_types=1);

namespace Concrete\NormalMode;
use Concrete\Entity\FoodEntity;
use Concrete\View\DeltaView;
use Exception;
use Model\Entity\IEntity;
use Model\Game\GameBase;
use Model\Player\IPlayer;

class NormalGame extends GameBase {
    private ?FoodEntity $food = null;

    public function __construct(int $width, int $height, int $maxPlayers) {
        // TODO: I'm really unsure about this, I think I want to pass a ready-made board instead of creating it
        $board = new NormalBoard($width, $height);
        $this->view = new DeltaView(); // TODO: probably same for this
        parent::__construct($board, $maxPlayers);
    }

    public function onStart(): void {
        $this->foodLoop();
    }

    protected function foodLoop(IEntity $consumable = null, IPlayer $player = null) {
        $this->food?->detachFromSlot();
        if (!($newSlot = $this->board->tryGetEmptySlot())) throw new Exception("Board is full of snake.");
        $food = $this->food = new FoodEntity();
        $food->sizeValue = rand(1, 2);
        $food->scoreValue = rand(1, 2);
        $food->setCollideCallback($this->foodLoop(...));
        $newSlot->add($this->food);
    }

    protected function getNewPlayer(int $fd, string $name): IPlayer {
        return new NormalPlayer($fd, $name);
    }
}
