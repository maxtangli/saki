<?php
namespace Saki\Win\Yaku;

use Saki\Win\TileSeries\FourConcealedTripleOrQuadAndOnePairTileSeries;
use Saki\Win\WaitingType;
use Saki\Win\WinSubTarget;

class FourConcealedTriplesOnePairWaitingYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 26;
    }

    protected function getExposedFanCount() {
        return 26;
    }

    protected function getRequiredTileSeries() {
        return [
            FourConcealedTripleOrQuadAndOnePairTileSeries::getInstance()
        ];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        $waitingType = FourConcealedTripleOrQuadAndOnePairTileSeries::getInstance()->getWaitingType($subTarget->getAllMeldList(), $subTarget->getWinTile());
        return $waitingType == WaitingType::getInstance(WaitingType::SINGLE_PAIR_WAITING);
    }

    function getExcludedYakus() {
        return [
            FourConcealedTriplesYaku::getInstance()
        ];
    }
}