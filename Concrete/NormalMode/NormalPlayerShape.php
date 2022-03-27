<?php declare(strict_types=1);

namespace Concrete\NormalMode;
use Concrete\Entity\PlayerEntity;
use Model\Direction;
use Model\Entity\IEntity;
use Model\Entity\PlayerType;
use Model\InternalMisuseException;
use Model\Player\Input;
use Model\Shape\ShapeBase;
use Model\Slot\ISlot;

class NormalPlayerShape extends ShapeBase {
    protected ?PlayerEntity $headEntity = null;
    /** @var PlayerEntity[] $bodyEntities */
    protected array $bodyEntities = [];

    public function __construct(protected int $fd) {}
    public function destroy(): void {
        if ($this->headEntity === null) throw new InternalMisuseException("Destroy for PlayerShape destroyed or not instantiated.");
        $this->headEntity->detachFromSlot(); //head can be out of bounds
        foreach ($this->bodyEntities as $entity) {
            $entity->detachFromSlot();
        }
    }

    public function move(Input $input): void {
        if ($this->headEntity === null) throw new InternalMisuseException("Move for PlayerShape destroyed or not instantiated.");
        if (!$oldSlot = $this->headEntity->tryGetSlot()) throw new InternalMisuseException("Move for PlayerShape out of bounds.");
        $x = $y = 0;
        $oldSlot->getPosition(outX: $x, outY: $y);
        $newDirection = $this->getNewDirection($input);
        $newDirection->move(refX: $x, refY: $y);
        $newSlot = $this->board->tryGetSlot($x, $y);
        $oldSlot = $this->moveHead($newSlot, $newDirection);

        $bodyCount = count($this->bodyEntities);
        $size = $this->size;

        if ($size == 0) return;
        if ($size > $bodyCount) $this->growBody($oldSlot, $newDirection);
        else {
            $this->moveBody($oldSlot, $newDirection);

            if ($this->size < $bodyCount) {
                $this->popLastBodyEntity();
            }
        }
    }
    protected function getNewDirection(Input $input): Direction {
        return $this->headEntity->getDirection()->rotate($input);
    }
    public function tryGetSlot(): ?ISlot {
        if ($this->headEntity === null) throw new InternalMisuseException("GetSlot for PlayerShape destroyed or not instantiated.");
        return $this->headEntity->tryGetSlot();
    }
    protected function moveHead(?ISlot $newSlot, Direction $direction): ?ISlot {
        $headEntity = $this->headEntity;
        $headEntity->setDirection($direction);
        $oldSlot = $headEntity->tryGetSlot();
        $headEntity->detachFromSlot();
        $newSlot?->add($headEntity);
        return $oldSlot;
    }

    protected function growBody(ISlot $oldHeadSlot, Direction $newDirection): void {
        $newBody = new PlayerEntity($this->fd, PlayerType::BODY);
        $newBody->setDirection($newDirection);
        $oldHeadSlot->add($newBody);
        $this->bodyEntities[] = $newBody;
    }

    protected function moveBody(ISlot $oldHeadSlot, Direction $newDirection): void {
        $lastBody = $this->popLastBodyEntity();
        $lastBody->setDirection($newDirection);
        $oldHeadSlot->add($lastBody);
        $this->bodyEntities[] = $lastBody;
    }

    protected function popLastBodyEntity(): PlayerEntity {
        $lastBody = $this->bodyEntities[0];
        $lastBody->detachFromSlot();
        unset($this->bodyEntities[0]);
        $this->bodyEntities = array_values($this->bodyEntities);
        return $lastBody;
    }

    protected function instantiate(ISlot $slot, ?Direction $direction = null): void {
        $x = $y = 0;
        $slot->getPosition(outX: $x, outY: $y);
        $direction = $direction ?? $x > $this->board->getWidth() ? Direction::LEFT : Direction::RIGHT;
        $entity = new PlayerEntity($this->fd, PlayerType::HEAD);
        $entity->setDirection($direction);
        $slot->add($entity);
        $this->headEntity = $entity;
    }

    public function getEntity(): IEntity {
        if ($this->headEntity === null) throw new InternalMisuseException("getEntity for PlayerShape destroyed or not instantiated.");
        return $this->headEntity;
    }
}