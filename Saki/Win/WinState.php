<?php
namespace Saki\Win;

use Saki\Util\Enum;

class WinState extends Enum {
    /**
     * なし
     */
    const NOT_WIN = 1;
    /**
     * 振り聴
     */
    const DISCARDED_TILE_FALSE_WIN = 2;
    /**
     * 役なし
     */
    const NO_YAKU_FALSE_WIN = 3;
    /**
     * ロン・ツモ
     */
    const WIN = 4;

    const WIN_BY_SELF = 4;
    const WIN_BY_OTHER = 5;

    static function getNotWinInstance() {
        return self::getInstance(self::NOT_WIN);
    }

    static function getDiscardedTileFalseWinInstance() {
        return self::getInstance(self::DISCARDED_TILE_FALSE_WIN);
    }

    static function getNoYakuFalseWinInstance() {
        return self::getInstance(self::NO_YAKU_FALSE_WIN);
    }

    static function getWinInstance() {
        return self::getInstance(self::WIN);
    }

    static function getValue2StringMap() {
        return [
            self::NOT_WIN => 'not win',
            self::DISCARDED_TILE_FALSE_WIN => 'discarded tile false win',
            self::NO_YAKU_FALSE_WIN => 'no yaku false win',
            self::WIN => 'win',
        ];
    }
}