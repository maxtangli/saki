<?php
namespace Saki\Win\Yaku;

use Saki\Win\TileSeries\FourConcealedTripleOrQuadAndOnePairTileSeries;
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
            FourConcealedTripleOrQuadAndOnePairTileSeries::getInstance()
        ];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return true;
    }
}

