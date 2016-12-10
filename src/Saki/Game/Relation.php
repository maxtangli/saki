<?php

namespace Saki\Game;

use Saki\Util\Enum;

/**
 * @package Saki\Game
 */
class Relation extends Enum {
    const SELF = 0;
    const NEXT = 1;
    const TOWARDS = 2;
    const PREV = 3;

    /**
     * @param SeatWind $target
     * @param SeatWind $self
     * @return Relation
     */
    static function createByTarget(SeatWind $target, SeatWind $self) {
        $i = $self->getNormalizedOffsetTo($target, 4);
        return new self($i);
    }

    /**
     * @param int $n
     * @return int
     */
    function toFromIndex(int $n) {
        switch ($this->getValue()) {
            case self::SELF:
            case self::PREV:
                return 0;
            case self::TOWARDS:
                return 1;
            case self::NEXT:
                return $n - 1;
        }
        throw new \LogicException();
    }

    /**
     * @param int $n
     * @return int
     */
    function toSecondFromIndex(int $n) {
        $i = $this->toFromIndex($n);
        return $i < $n - 1 ? $i + 1 : $i - 1;
    }
}