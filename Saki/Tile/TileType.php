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
        return [self::getInstance(self::CHARACTER_M), self::getInstance(self::DOT_P), self::getInstance(self::BAMBOO_S)];
    }

    function isSuit() {
        return !$this->isHonor();
    }

    function isHonor() {
        return $this->isWind() || $this->isDragon();
    }

    function isWind() {
        return in_array($this->getValue(), [self::EAST_E, self::SOUTH_S, self::WEST_W, self::NORTH_N]);
    }

    function isDragon() {
        return in_array($this->getValue(), [self::RED_C, self::WHITE_P, self::GREEN_F]);
    }

    // overrides return type

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
}