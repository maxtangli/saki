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
     * @param SeatWind $other
     * @param SeatWind $self
     * @return Relation
     */
    static function createByOther(SeatWind $other, SeatWind $self) {
        $i = $self->getNormalizedOffsetTo($other, 4);
        return self::create($i);
    }

    /**
     * @return self
     */
    static function createSelf() {
        return self::create(self::SELF);
    }

    /**
     * @param SeatWind $self
     * @return SeatWind
     */
    function toOther(SeatWind $self) {
        return $self->toNext($this->getToNextOffset());
    }

    /**
     * @return int
     */
    private function getToNextOffset() {
        return $this->getValue();
    }

    /**
     * @param int $n
     * @return int
     */
    function toDisplaySetIndex(int $n) {
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
}