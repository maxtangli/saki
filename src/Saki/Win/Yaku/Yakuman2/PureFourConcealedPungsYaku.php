<?php
namespace Saki\Win\Yaku\Yakuman2;

use Saki\Win\Series\Series;
use Saki\Win\Waiting\WaitingType;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;
use Saki\Win\Yaku\Yakuman\FourConcealedPungsYaku;

/**
 * 四暗刻単騎
 * @package Saki\Win\Yaku\Yakuman2
 */
class PureFourConcealedPungsYaku extends Yaku {
    function getConcealedFan() {
        return 26;
    }

    function getNotConcealedFan() {
        return 26;
    }

    function getRequiredSeries() {
        return [
            Series::create(Series::FOUR_WIN_SET_AND_ONE_PAIR)
        ];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        $isFourConcealedPungs = $subTarget->getAllMeldList()
            ->isFourPungsOrKongsAndAPair(true);

        $waitingType = Series::create(Series::FOUR_WIN_SET_AND_ONE_PAIR)
            ->getWaitingType($subTarget->getSubHand());
        $isPairWaiting = ($waitingType == WaitingType::create(WaitingType::PAIR_WAITING));

        return $isFourConcealedPungs && $isPairWaiting;
    }

    function getExcludedYakus() {
        return [FourConcealedPungsYaku::create()];
    }
}