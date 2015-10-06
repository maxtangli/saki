<?php
namespace Saki\Win\Yaku;

use Saki\Win\TileSeries;
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
            TileSeries::getInstance(TileSeries::FOUR_CONCEALED_TRIPLE_OR_QUAD_AND_ONE_PAIR)
        ];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        $waitingType = TileSeries::getInstance(TileSeries::FOUR_CONCEALED_TRIPLE_OR_QUAD_AND_ONE_PAIR)->getWaitingType($subTarget->getAllMeldList(), $subTarget->getTargetTile());
        return $waitingType == WaitingType::getInstance(WaitingType::PAIR_WAITING);
    }

    function getExcludedYakus() {
        return [
            FourConcealedTriplesYaku::getInstance()
        ];
    }
}