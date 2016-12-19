<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Win\Series\Series;
use Saki\Win\Waiting\WaitingType;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * 平和
 * @package Saki\Win\Yaku
 */
class PinfuYaku extends Yaku {
    function getConcealedFan() {
        return 1;
    }

    function getNotConcealedFan() {
        return 0;
    }

    function getRequiredSeries() {
        return [
            Series::create(Series::FOUR_WIN_SET_AND_ONE_PAIR)
        ];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        /**
         * 平和の成立条件
         * 1. 門前
         * 2. 4面子すべてが順子
         * 3. 雀頭は役牌ではない
         * 4. 両面待ち
         * @see https://ja.wikipedia.org/wiki/平和_(麻雀)
         */
        $isFourChowAndOnePair = $subTarget->getAllMeldList()->isFourChowAndOnePair();

        $isAllSuit = $subTarget->getHand()->getComplete()->isAllSuit();

        $waitingType = Series::create(Series::FOUR_WIN_SET_AND_ONE_PAIR)
            ->getWaitingType($subTarget->getSubHand());
        $isTwoSideWaiting = ($waitingType == WaitingType::create(WaitingType::TWO_SIDE_CHOW_WAITING));

        return $isFourChowAndOnePair && $isAllSuit && $isTwoSideWaiting;
    }
}

