<?php
namespace Saki\Win\Yaku\Yakuman;

use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class ThirteenOrphansYaku extends Yaku {
    function getConcealedFan() {
        return 13;
    }

    function getNotConcealedFan() {
        return 13;
    }

    function getRequiredTileSeries() {
        return [
            TileSeries::create(TileSeries::THIRTEEN_ORPHANS)
        ];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isThirteenOrphan(false);
    }
}

