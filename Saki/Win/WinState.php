<?php
namespace Saki\Win;

use Saki\Util\Enum;
use Saki\Util\Utils;

class WinState extends Enum {
    static function getComparator() {
        $descBestOnes = [
            WinState::getInstance(self::FURITEN_FALSE_WIN),
            WinState::getInstance(self::WIN_BY_SELF),
            WinState::getInstance(self::WIN_BY_OTHER),
            WinState::getInstance(self::NO_YAKU_FALSE_WIN),
            WinState::getInstance(self::WAITING_BUY_NOT_WIN),
            WinState::getInstance(self::NOT_WIN),
        ];
        return Utils::getComparatorByBestArray($descBestOnes);
    }

    function compareTo($other) {
        $f = $this->getComparator();
        return $f($this, $other);
    }

    const NOT_WIN = 1; // なし
    const WAITING_BUY_NOT_WIN = 2; // 聴牌
    const FURITEN_FALSE_WIN = 2; // 振り聴
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

    function isWaiting() {
        return $this->getValue() != self::NOT_WIN;
    }

    function isTrueWin() {
        $targetValues = [self::WIN_BY_SELF, self::WIN_BY_OTHER];
        return in_array($this->getValue(), $targetValues);
    }

    function isFalseWin() {
        $targetValues = [self::FURITEN_FALSE_WIN, self::NO_YAKU_FALSE_WIN];
        return in_array($this->getValue(), $targetValues);
    }

    function isTrueOrFalseWin() {
        return $this->isTrueWin() || $this->isFalseWin();
    }
}