<?php
namespace Saki\RoundResult;

use Saki\Util\Enum;

class RoundResultType extends Enum {
    const WIN_BY_SELF = 0;
    const WIN_BY_OTHER = 1;
    const DOUBLE_WIN_BY_OTHER = 2;
    const TRIPLE_WIN_BY_OTHER = 3;

    const EXHAUSTIVE_DRAW = 4;

    const NINE_KINDS_OF_TERMINAL_OR_HONOR_DRAW = 5;
    const FOUR_WIND_DRAW = 6;
    const FOUR_KONG_DRAW = 7;
    const FOUR_REACH_DRAW = 8;

    function isWin() {
        return $this->isTargetValue([
            self::WIN_BY_SELF, self::WIN_BY_OTHER, self::DOUBLE_WIN_BY_OTHER, self::TRIPLE_WIN_BY_OTHER]);
    }

    function isWinByOther() {
        return $this->isTargetValue([self::WIN_BY_OTHER, self::DOUBLE_WIN_BY_OTHER, self::TRIPLE_WIN_BY_OTHER]);
    }

    function isMultiWinByOther() {
        return $this->isTargetValue([self::DOUBLE_WIN_BY_OTHER, self::TRIPLE_WIN_BY_OTHER]);
    }

    function isDraw() {
        return !$this->isWin();
    }

    function isOnTheWayDraw() {
        return $this->isTargetValue([
            self::NINE_KINDS_OF_TERMINAL_OR_HONOR_DRAW, self::FOUR_WIND_DRAW, self::FOUR_KONG_DRAW, self::FOUR_REACH_DRAW]);
    }

    /**
     * @param $value
     * @return RoundResultType
     */
    static function getInstance($value) {
        return parent::getInstance($value);
    }
}