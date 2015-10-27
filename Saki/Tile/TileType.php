<?php
namespace Saki\Tile;

use Saki\Util\Enum;

class TileType extends Enum {

    const CHARACTER_M = 1;
    const DOT_P = 2;
    const BAMBOO_S = 3;

    const EAST_E = 4;
    const SOUTH_S = 5;
    const WEST_W = 6;
    const NORTH_N = 7;

    const RED_C = 8;
    const WHITE_P = 9;
    const GREEN_F = 10;

    const REGEX_SUIT_TYPE = '[smp]';
    const REGEX_HONOR_TYPE = '[ESWNCPF]';

    static function getValue2StringMap() {
        return [
            self::CHARACTER_M => 'm',
            self::DOT_P => 'p',
            self::BAMBOO_S => 's',

            self::EAST_E => 'E',
            self::SOUTH_S => 'S',
            self::WEST_W => 'W',
            self::NORTH_N => 'N',

            self::RED_C => 'C',
            self::WHITE_P => 'P',
            self::GREEN_F => 'F',
        ];
    }

    static function getSuitTypes() {
        return [
            self::getInstance(self::BAMBOO_S), self::getInstance(self::CHARACTER_M), self::getInstance(self::DOT_P),
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
            case self::BAMBOO_S:
            case self::CHARACTER_M:
            case self::DOT_P;
                return true;
            default;
                return false;
        }
    }

    function isHonor() {
        switch ($this->getValue()) {
            case self::EAST_E:
            case self::WEST_W:
            case self::SOUTH_S;
            case self::NORTH_N;
            case self::RED_C;
            case self::GREEN_F;
            case self::WHITE_P;
                return true;
            default;
                return false;
        }
    }

    function isWind() {
        switch ($this->getValue()) {
            case self::EAST_E:
            case self::WEST_W:
            case self::SOUTH_S;
            case self::NORTH_N;
                return true;
            default;
                return false;
        }
    }

    function isDragon() {
        switch ($this->getValue()) {
            case self::RED_C;
            case self::GREEN_F;
            case self::WHITE_P;
                return true;
            default;
                return false;
        }
    }
}