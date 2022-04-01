<?php

namespace Model\Factory;

/**
 * @template T
 * @implements IFactory<T>
 */
abstract class FactoryBase implements IFactory {
    /** @var class-string<T> */
    protected string $classString;

    /** @param class-string<T> $classString */
    public function __construct(string $classString) {
        $this->classString = $classString;
    }

    /** @return T */
    public function getNewInstance(...$arguments): mixed {
        return new $this->classString(...$arguments);
    }
}