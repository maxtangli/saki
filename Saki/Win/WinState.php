<?php
namespace Saki\Win;

use Saki\Util\Enum;

class WinState extends Enum {
    /**
     * ノー聴
     */
    const NOT_WIN_TILES = 1;
    /**
     * 振り聴
     */
    const DISCARDED_WIN_TILE = 2;
    /**
     * 役なし
     */
    const NO_YAKU = 3;
    /**
     * ロン・ツモ
     */
    const WIN = 4;

    static function getNotWinTilesInstance() {
        return self::getInstance(self::NOT_WIN_TILES);
    }

    static function getDiscardedWinTileInstance() {
        return self::getInstance(self::DISCARDED_WIN_TILE);
    }

    static function getNoYakuInstance() {
        return self::getInstance(self::NO_YAKU);
    }

    static function getWinInstance() {
        return self::getInstance(self::WIN);
    }

    static function getValue2StringMap() {
        return [
            self::NOT_WIN_TILES => 'not win tiles',
            self::DISCARDED_WIN_TILE => 'discarded win tile',
            self::NO_YAKU => 'no yaku',
            self::WIN => 'win',
        ];
    }
}