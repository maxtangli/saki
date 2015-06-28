<?php
namespace Saki\Yaku;

use Saki\Tile;

class AllRunsYaku extends Yaku {
    function getConcealedFanCount() {
        return 1;
    }

    function getExposedFanCount() {
        return 0;
    }

    function existInImpl(YakuAnalyzerSubTarget $subTarget) {
        /**
         * https://ja.wikipedia.org/wiki/%E5%B9%B3%E5%92%8C_(%E9%BA%BB%E9%9B%80)
         * 平和の成立条件は以下の4つである。
         * - 門前であること。すなわちチーをしてはいけない（下の条件2によりポンやカンは不可能である）。
         * - 符のつかない面子で手牌が構成されていること。すなわち4面子すべてが順子であること。
         * - 符のつかない対子が雀頭であること、すなわち役牌が雀頭の時は平和にならない。
         * - 符のつかない待ち、すなわち辺張待ち・嵌張待ち・単騎待ちではなく、両面待ちであること。
         */
        return $subTarget->is4RunAnd1Pair()
        && $subTarget->isAllSuit()
        && $subTarget->isTwoSidesRunWaiting();

        // winInfo.winningTile is not middle in sequence(at least one case)
    }
}

