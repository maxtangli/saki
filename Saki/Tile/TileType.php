<?php
namespace Saki\Tile;

use Saki\Util\Enum;

class TileType extends Enum {
    const BAMBOO = 1;
    const CHARACTER = 2;
    const DOT = 3;
    const EAST = 4;
    const SOUTH = 5;
    const WEST = 6;
    const NORTH = 7;
    const WHITE = 8;
    const GREEN = 9;
    const RED = 10;

    const REGEX_SUIT_TYPE = '[smp]';
    const REGEX_HONOR_TYPE = '[ESWNCFP]';

    static function getValue2StringMap() {
        return [
            self::BAMBOO => 's',
            self::CHARACTER => 'm',
            self::DOT => 'p',
            self::EAST => 'E',
            self::SOUTH => 'S',
            self::WEST => 'W',
            self::NORTH => 'N',
            self::WHITE => 'P',
            self::GREEN => 'F',
            self::RED => 'C',
        ];
    }

    /**
     * @param $value
     * @return TileType
     */
    static function getInstance($value) {
        return parent::getInstance($value);
    }

    /**
     * @param string $s
     * @return TileType
     */
    static function fromString($s) {
        return parent::fromString($s);
    }

    function isSuit() {
        switch ($this->getValue()) {
            case self::BAMBOO:
            case self::CHARACTER:
            case self::DOT;
                return true;
            default;
                return false;
        }
    }

    function isHonor() {
        switch ($this->getValue()) {
            case self::EAST:
            case self::WEST:
            case self::SOUTH;
            case self::NORTH;
            case self::RED;
            case self::GREEN;
            case self::WHITE;
                return true;
            default;
                return false;
        }
    }

    function isWind() {
        switch ($this->getValue()) {
            case self::EAST:
            case self::WEST:
            case self::SOUTH;
            case self::NORTH;
                return true;
            default;
                return false;
        }
    }

    function isDragon() {
        switch ($this->getValue()) {
            case self::RED;
            case self::GREEN;
            case self::WHITE;
                return true;
            default;
                return false;
        }
    }
}