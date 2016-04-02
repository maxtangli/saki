<?php
namespace Saki\Tile;

use Saki\Util\Enum;
use Saki\Util\Utils;

class TileType extends Enum {
    const REGEX_SUIT_TYPE = '[smp]';
    const REGEX_HONOR_TYPE = '[ESWNCPF]';

    /**
     * Tile ID concerned
     */
    const CHARACTER_M = 100;
    const DOT_P = 200;
    const BAMBOO_S = 300;
    const EAST_E = 410;
    const SOUTH_S = 420;
    const WEST_W = 430;
    const NORTH_N = 440;
    const RED_C = 510;
    const WHITE_P = 520;
    const GREEN_F = 530;

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
        return $this->getValue() <= 300;
    }

    function isHonor() {
        return Utils::inRange($this->getValue(), 410, 530);
    }

    function isWind() {
        return Utils::inRange($this->getValue(), 410, 440);
    }

    function isDragon() {
        return Utils::inRange($this->getValue(), 510, 530);
    }
}