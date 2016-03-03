<?php
namespace Saki\Win\Yaku\Yakuman2;

use Saki\Win\TileSeries;
use Saki\Win\WaitingType;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;
use Saki\Win\Yaku\Yakuman\FourConcealedTriplesYaku;

class FourConcealedTriplesOnePairWaitingYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 26;
    }

    protected function getNotConcealedFanCount() {
        return 26;
    }

    protected function getRequiredTileSeries() {
        return [
            TileSeries::getInstance(TileSeries::FOUR_CONCEALED_TRIPLE_OR_QUAD_AND_ONE_PAIR)
        ];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        $waitingType = TileSeries::getInstance(TileSeries::FOUR_CONCEALED_TRIPLE_OR_QUAD_AND_ONE_PAIR)->getWaitingType($subTarget->getAllMeldList(), $subTarget->getTileOfTargetTile());
        return $waitingType == WaitingType::getInstance(WaitingType::PAIR_WAITING);
    }

    function getExcludedYakus() {
        return [
            FourConcealedTriplesYaku::getInstance()
        ];
    }
}