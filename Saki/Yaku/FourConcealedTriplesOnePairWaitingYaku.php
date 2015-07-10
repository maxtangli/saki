<?php
namespace Saki\Yaku;

use Saki\Win\WinAnalyzerSubTarget;

class FourConcealedTriplesOnePairWaitingYaku extends Yaku {
    function getConcealedFanCount() {
        return 26;
    }

    function getExposedFanCount() {
        return 26;
    }

    function existInImpl(WinAnalyzerSubTarget $subTarget) {
        return $subTarget->is4TripleOrQuadAnd1Pair(true)
        && $subTarget->isOnePairWaiting();
    }

    function getExcludedYakus() {
        return [
            FourConcealedTriplesYaku::getInstance()
        ];
    }
}