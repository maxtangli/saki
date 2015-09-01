<?php
namespace Saki\Win\Yaku;

use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;

class FourConcealedTriplesYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 13;
    }

    protected function getExposedFanCount() {
        return 13;
    }

    protected function getRequiredTileSeries() {
        return [
            TileSeries::getInstance(TileSeries::FOUR_CONCEALED_TRIPLE_OR_QUAD_AND_ONE_PAIR)
        ];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return true;
    }
}

