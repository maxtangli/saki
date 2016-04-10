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
     * @return RoundWind
     */
    function getLastRoundWind() {
        switch ($this->getValue()) {
            case self::EAST:
                return RoundWind::fromString('E');
            case self::EAST_SOUTH:
                return RoundWind::fromString('S');
            case self::FULL;
                return RoundWind::fromString('N');
        }
        throw new \LogicException();
    }

    /**
     * @param RoundWind $roundWind
     * @return bool
     */
    function inLength(RoundWind $roundWind) {
        switch ($this->getValue()) {
            case self::EAST:
                return $roundWind == RoundWind::fromString('E');
            case self::EAST_SOUTH:
                return $roundWind == RoundWind::fromString('E') || $roundWind == RoundWind::fromString('S');
            case self::FULL;
                return true;
        }
        throw new \LogicException();
    }
}