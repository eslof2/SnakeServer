<?php

namespace Model\Factory;

/** @template T */
interface IFactory {
    /** @return T */
    function getNewInstance(): mixed;
}