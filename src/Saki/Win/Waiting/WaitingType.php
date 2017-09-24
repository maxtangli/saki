<?php

namespace Saki\Win\Waiting;

use Saki\Util\ComparablePriority;
use Saki\Util\Enum;

/**
 * @package Saki\Win\Waiting
 */
class WaitingType extends Enum {
    use ComparablePriority;

    /**
     * @return int
     */
    function getPriority() {
        $m = [
            /**
             * multiple waiting type may exist e.g.
             * - 1123m+1m: two-side, pair.
             * - 1233m+3m: one-side, pair.
             * - 1223m+2m: middle-chow, pair.
             *
             * yaku case
             * - PinfuYaku: require two-side. multiple waiting type MAY exist.
             * - PureFourConcealedPungsYaku: require pair-waiting. multiple waiting type WON'T exist.
             * fu case
             * - chose highest one: one-side = middle-chow = pair-waiting > two-side = triple-waiting
             * */
            self::ORPHAN_WAITING => 7,
            self::ONE_SIDE_CHOW_WAITING => 6,
            self::MIDDLE_CHOW_WAITING => 5,
            self::PAIR_WAITING => 4,
            self::TWO_SIDE_CHOW_WAITING => 3,
            self::TRIPLE_WAITING => 2,
            self::NOT_WAITING => 1,
        ];
        return $m[$this->getValue()];
    }

    const NOT_WAITING = 0; // ノー聴
    const TWO_SIDE_CHOW_WAITING = 1; // 両面待ち 78 -> 678 or 789
    const ONE_SIDE_CHOW_WAITING = 2; // 辺張待ち 89 -> 789
    const MIDDLE_CHOW_WAITING = 3; // 嵌張待ち 7 9 -> 789
    const PAIR_WAITING = 4; // 単騎待ち 1 -> 11
    const TRIPLE_WAITING = 5; // 双碰待ち 11 -> 111
    const ORPHAN_WAITING = 6; // 国士無双

    /**
     * @return bool
     */
    function exist() {
        return $this->getValue() != self::NOT_WAITING;
    }
}