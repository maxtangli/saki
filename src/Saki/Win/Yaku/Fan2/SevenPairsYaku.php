<?php
namespace Saki\Win\Yaku\Fan2;

use Saki\Win\Series;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class SevenPairsYaku extends Yaku {
    function getConcealedFan() {
        return 2;
    }

    function getNotConcealedFan() {
        return 0;
    }

    function getRequiredSeries() {
        return [
            Series::create(Series::SEVEN_PAIRS)
        ];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return true;
    }
}

