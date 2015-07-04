<?php
namespace Saki\Game\RoundResult;

use Saki\Util\Enum;

class RoundResultType extends Enum {
    const WIN_BY_SELF = 1;
    const SINGLE_WIN_BY_OTHER = 1;
    const MULTIPLE_WIN_BY_OTHER = 1;
    const EXHAUSTIVE_DRAW = 4;

    static function getValue2StringMap() {
        return [
            self::WIN_BY_SELF => 'win by self',
            self::SINGLE_WIN_BY_OTHER => 'single win by other',
            self::MULTIPLE_WIN_BY_OTHER => 'multiple win by other',
            self::EXHAUSTIVE_DRAW => 'exhaustive draw',
        ];
    }

    static function getWinBySelfInstance() {
        return self::getInstance(self::WIN_BY_SELF);
    }

    static function getSingleWinByOtherInstance() {
        return self::getInstance(self::SINGLE_WIN_BY_OTHER);
    }

    static function getMultipleWinByOtherInstance() {
        return self::getInstance(self::MULTIPLE_WIN_BY_OTHER);
    }

    static function getExhaustiveDrawInstance() {
        return self::getInstance(self::EXHAUSTIVE_DRAW);
    }
}