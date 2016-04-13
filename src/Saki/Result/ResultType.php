<?php
namespace Saki\Result;

use Saki\Util\Enum;

/**
 * @package Saki\Result
 */
class ResultType extends Enum {
    const WIN_BY_SELF = 0;
    const WIN_BY_OTHER = 1;
    const DOUBLE_WIN_BY_OTHER = 2;
    const TRIPLE_WIN_BY_OTHER = 3;
    const EXHAUSTIVE_DRAW = 4;
    const NINE_NINE_DRAW = 5;
    const FOUR_WIND_DRAW = 6;
    const FOUR_KONG_DRAW = 7;
    const FOUR_REACH_DRAW = 8;

    /**
     * @return bool
     */
    function isWin() {
        return $this->isWinBySelf() || $this->isWinByOther();
    }

    /**
     * @return bool
     */
    function isWinBySelf() {
        return $this->isTargetValue([
            self::WIN_BY_SELF
        ]);
    }

    /**
     * @return bool
     */
    function isWinByOther() {
        return $this->isTargetValue([
            self::WIN_BY_OTHER,
            self::DOUBLE_WIN_BY_OTHER,
            self::TRIPLE_WIN_BY_OTHER
        ]);
    }

    /**
     * @return bool
     */
    function isMultiWinByOther() {
        return $this->isTargetValue([
            self::DOUBLE_WIN_BY_OTHER,
            self::TRIPLE_WIN_BY_OTHER
        ]);
    }

    /**
     * @return bool
     */
    function isDraw() {
        return $this->isExhaustiveDraw() || $this->isAbortiveDraw();
    }

    /**
     * @return bool
     */
    function isExhaustiveDraw() {
        return $this->isTargetValue([
            self::EXHAUSTIVE_DRAW
        ]);
    }

    /**
     * @return bool
     */
    function isAbortiveDraw() {
        return $this->isTargetValue([
            self::NINE_NINE_DRAW,
            self::FOUR_WIND_DRAW,
            self::FOUR_KONG_DRAW,
            self::FOUR_REACH_DRAW
        ]);
    }
}