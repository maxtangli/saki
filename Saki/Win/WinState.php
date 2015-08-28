<?php
namespace Saki\Win;

use Saki\Util\Enum;

class WinState extends Enum {
    const NOT_WIN = 1; // なし
    const DISCARDED_TILE_FALSE_WIN = 2; // 振り聴
    const NO_YAKU_FALSE_WIN = 3; // 役なし
    const WIN_BY_SELF = 4; // ツモ
    const WIN_BY_OTHER = 5; // ロン

    /**
     * @param $value
     * @return WinState
     */
    static function getInstance($value) {
        return parent::getInstance($value);
    }

    function isTrueWin() {
        $targetValues = [self::WIN_BY_SELF, self::WIN_BY_OTHER];
        return in_array($this->getValue(), $targetValues);
    }

    function isFalseWin() {
        $targetValues = [self::DISCARDED_TILE_FALSE_WIN, self::NO_YAKU_FALSE_WIN];
        return in_array($this->getValue(), $targetValues);
    }
}