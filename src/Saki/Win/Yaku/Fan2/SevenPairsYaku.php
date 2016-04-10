<?php
namespace Saki\Win\Yaku\Fan2;

use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class SevenPairsYaku extends Yaku {
    function getConcealedFan() {
        return 2;
    }

    function getNotConcealedFan() {
        return 0;
    }

    function getRequiredTileSeries() {
        return [
            TileSeries::create(TileSeries::SEVEN_PAIRS)
        ];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return true;
    }
}

