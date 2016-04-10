<?php
namespace Saki\Win\Yaku\Yakuman;

use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class ThirteenOrphansYaku extends Yaku {
    function getConcealedFanCount() {
        return 13;
    }

    function getNotConcealedFanCount() {
        return 13;
    }

    function getRequiredTileSeries() {
        return [
            TileSeries::create(TileSeries::THIRTEEN_ORPHANS)
        ];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isThirteenOrphan(false);
    }
}

