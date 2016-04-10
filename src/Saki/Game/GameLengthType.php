<?php
namespace Saki\Game;

use Saki\Util\Enum;

/**
 * @package Saki\Game
 */
class GameLengthType extends Enum {
    const EAST = 1;
    const EAST_SOUTH = 2;
    const FULL = 4;

    /**
     * @return PrevailingWind
     */
    function getLastPrevailingWind() {
        switch ($this->getValue()) {
            case self::EAST:
                return PrevailingWind::fromString('E');
            case self::EAST_SOUTH:
                return PrevailingWind::fromString('S');
            case self::FULL;
                return PrevailingWind::fromString('N');
        }
        throw new \LogicException();
    }

    /**
     * @param PrevailingWind $prevailingWind
     * @return bool
     */
    function inLength(PrevailingWind $prevailingWind) {
        switch ($this->getValue()) {
            case self::EAST:
                return $prevailingWind == PrevailingWind::fromString('E');
            case self::EAST_SOUTH:
                return $prevailingWind == PrevailingWind::fromString('E') || $prevailingWind == PrevailingWind::fromString('S');
            case self::FULL;
                return true;
        }
        throw new \LogicException();
    }
}