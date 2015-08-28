<?php
namespace Saki\Win\Yaku;

use Saki\Win\TileSeries\FourRunAndOnePairTileSeries;
use Saki\Win\WaitingType;
use Saki\Win\WinSubTarget;

/**
 * 平和
 * @package Saki\Win\Yaku
 */
class AllRunsYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 1;
    }

    protected function getExposedFanCount() {
        return 0;
    }

    protected function getRequiredTileSeries() {
        return [
            FourRunAndOnePairTileSeries::getInstance()
        ];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        /**
         * https://ja.wikipedia.org/wiki/%E5%B9%B3%E5%92%8C_(%E9%BA%BB%E9%9B%80)
         * 平和の成立条件は以下の4つである。
         * - 門前であること。すなわちチーをしてはいけない（下の条件2によりポンやカンは不可能である）。
         * - 符のつかない面子で手牌が構成されていること。すなわち4面子すべてが順子であること。
         * - 符のつかない対子が雀頭であること、すなわち役牌が雀頭の時は平和にならない。
         * - 符のつかない待ち、すなわち辺張待ち・嵌張待ち・単騎待ちではなく、両面待ちであること。
         */

        $isTwoSideRunWaiting = FourRunAndOnePairTileSeries::getInstance()->getWaitingType(
                $subTarget->getAllMeldList(), $subTarget->getWinTile()
            ) == WaitingType::getInstance(WaitingType::TWO_SIDE_RUN_WAITING);
        return $subTarget->isAllSuit() && $isTwoSideRunWaiting;
    }
}

