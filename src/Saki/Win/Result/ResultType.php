<?php
namespace Saki\Win\Result;

use Saki\Util\Enum;

/**
 * @package Saki\Win\Result
 */
class ResultType extends Enum {
    const WIN_BY_SELF = 0;
    const WIN_BY_OTHER = 1;
    const DOUBLE_WIN_BY_OTHER = 2;
    const EXHAUSTIVE_DRAW = 3;
    const NINE_NINE_DRAW = 4;
    const FOUR_WIND_DRAW = 5;
    const FOUR_KONG_DRAW = 6;
    const FOUR_REACH_DRAW = 7;
    const NAGASHIMANGAN_DRAW = 8;
    const TRIPLE_WIN_DRAW = 9;

    /**
     * @return bool
     */
    function isWin() {
        return $this->isTsumo() || $this->isRon();
    }

    /**
     * @return bool
     */
    function isTsumo() {
        return $this->inTargetValues([
            self::WIN_BY_SELF
        ]);
    }

    /**
     * @return bool
     */
    function isRon() {
        return $this->inTargetValues([
            self::WIN_BY_OTHER,
            self::DOUBLE_WIN_BY_OTHER,
        ]);
    }

    /**
     * @return bool
     */
    function isDraw() {
        return $this->isExhaustiveDraw()
            || $this->isAbortiveDraw()
            || $this->isNagashiManganDraw()
            || $this->isTripleWinDraw();
    }

    /**
     * @return bool
     */
    function isExhaustiveDraw() {
        return $this->inTargetValues([
            self::EXHAUSTIVE_DRAW
        ]);
    }

    /**
     * @return bool
     */
    function isAbortiveDraw() {
        return $this->inTargetValues([
            self::NINE_NINE_DRAW,
            self::FOUR_WIND_DRAW,
            self::FOUR_KONG_DRAW,
            self::FOUR_REACH_DRAW
        ]);
    }

    /**
     * @return bool
     */
    function isNagashiManganDraw() {
        return $this->inTargetValues([
            self::NAGASHIMANGAN_DRAW
        ]);
    }

    /**
     * @return bool
     */
    function isTripleWinDraw() {
        return $this->inTargetValues([
            self::TRIPLE_WIN_DRAW
        ]);
    }
}