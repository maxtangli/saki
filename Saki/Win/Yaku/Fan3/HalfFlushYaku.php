<?php
namespace Saki\Win\Yaku\Fan3;

use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class HalfFlushYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 3;
    }

    protected function getExposedFanCount() {
        return 2;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllTileSortedList(true)->isFlush(false);
    }
}


