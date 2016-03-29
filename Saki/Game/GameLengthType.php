<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\Enum;

class GameLengthType extends Enum {
    const EAST = 1;
    const EAST_SOUTH = 2;
    const FULL = 4;

    function getLastRoundWind() {
        switch ($this->getValue()) {
            case self::EAST:
                return Tile::fromString('E');
            case self::EAST_SOUTH:
                return Tile::fromString('S');
            case self::FULL;
                return Tile::fromString('N');
        }
        throw new \LogicException();
    }

    function isInLengthRoundWind(Tile $roundWind) {
        switch ($this->getValue()) {
            case self::EAST:
                return $roundWind == Tile::fromString('E');
            case self::EAST_SOUTH:
                return $roundWind == Tile::fromString('E') || $roundWind == Tile::fromString('S');
            case self::FULL;
                return true;
        }
        throw new \LogicException();
    }
}