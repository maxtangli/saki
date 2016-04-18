<?php
namespace Saki\Win\Yaku\Yakuman2;

use Saki\Win\Series;
use Saki\Win\WaitingType;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;
use Saki\Win\Yaku\Yakuman\FourConcealedTriplesYaku;

class FourConcealedTriplesOnePairWaitingYaku extends Yaku {
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
        $isFourConcealedTriples = $subTarget->getAllMeldList()->isFourTripleOrQuadAndOnePair(true);

        $waitingType = Series::create(Series::FOUR_WIN_SET_AND_ONE_PAIR)->getWaitingType(
            $subTarget->getAllMeldList(), $subTarget->getTileOfTargetTile(), $subTarget->getDeclaredMeldList()
        );
        $isPairWaiting = $waitingType == WaitingType::create(WaitingType::PAIR_WAITING);

        return $isFourConcealedTriples && $isPairWaiting;
    }

    function getExcludedYakus() {
        return [
            FourConcealedTriplesYaku::create()
        ];
    }
}