<?php declare(strict_types=1);

namespace Model\Player;
use Closure;
use Model\Board\IBoard;
use Model\Direction;
use Model\Shape\IShape;
use Model\State\IState;

interface IPlayer extends IState {
    public function getFd(): int;
    public function getName(): string;
    public function getShape(): IShape;
    public function processMovement(): void;
    public function processCollision(): void;
    /** @var null|Closure(IPlayer):void */
    public function setDeathCallback(?Closure $onDeath): void; // TODO: abstract the callback pattern
    public function setBoard(IBoard $board): void;
    public function setInput(Input $input): void;
}