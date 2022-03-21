<?php declare(strict_types=1);

namespace Model\Player;
use Model\Board\IBoard;
use Model\Direction;
use Model\Dirty\DirtyT;
use Model\Shape\IShape;
use Model\State\StateT;

abstract class PlayerBase implements IPlayer {
    use StateT;
    use DirtyT;

    protected IBoard $board;
    protected Input $input;
    protected IShape $shape;
    /** @var null|Closure(I):void */
    protected ?\Closure $deathCallback;

    public function __construct(
        public int $fd,
        public string $name
    ) {}

    public function getFd(): int {
        return $this->fd;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getShape(): IShape {
        return $this->shape;
    }

    public function processMovement(): void {
        $this->shape->move($this->input);
    }

    public function processCollision(): void {
        $slot = $this->shape->tryGetSlot();
        if (!$slot) { //player hit board boundaries
            $this->setHealth(0);
        } else {
            foreach ($slot->getEntities() as $entity) {
                $entity->collide($this);
            }
        }

        if ($this->isDead()) {
            $this->shape->destroy();
            if (!is_null($this->deathCallback)) ($this->deathCallback)($this);
        }
    }

    public function setBoard(IBoard $board): void {
        $this->board = $board;
    }

    public function setDeathCallback(?\Closure $onDeath): void {
        $this->deathCallback = $onDeath;
    }

    public function setInput(Input $input): void {
        $this->input = $input;
    }

    // we let inherited class decide what type of player and how it's instantiated
    public abstract function tryInstantiate(?int $x = null, ?int $y = null, Direction $direction = null): bool;
}