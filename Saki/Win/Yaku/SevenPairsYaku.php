<?php
namespace Saki\Win\Yaku;

use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;

class SevenPairsYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 2;
    }

    protected function getExposedFanCount() {
        return 0;
    }

    protected function getRequiredTileSeries() {
        return [
            TileSeries::getInstance(TileSeries::SEVEN_PAIRS)
        ];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return true;
    }
}

