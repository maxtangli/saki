<?php
namespace Saki\Win\Result;

use Saki\Util\Enum;

/**
 * @package Saki\Win\Result
 */
class ResultType extends Enum {
    const TSUMO_WIN = 0;
    const RON_WIN = 1;
    const DOUBLE_RON_WIN = 2;
    const EXHAUSTIVE_DRAW = 3;
    const NINE_NINE_DRAW = 4;
    const FOUR_WIND_DRAW = 5;
    const FOUR_KONG_DRAW = 6;
    const FOUR_REACH_DRAW = 7;
    const NAGASHIMANGAN_DRAW = 8;
    const TRIPLE_RON_DRAW = 9;

    /**
     * @return bool
     */
    function isWin() {
        return $this->inTargetValues([
            self::TSUMO_WIN,
            self::RON_WIN,
            self::DOUBLE_RON_WIN,
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
            self::TRIPLE_RON_DRAW
        ]);
    }
}