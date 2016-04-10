<?php
namespace Saki\Win\Yaku\Yakuman2;

use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;
use Saki\Win\Yaku\Yakuman\ThirteenOrphansYaku;

class ThirteenOrphansPairWaitingYaku extends Yaku {
    function getConcealedFan() {
        return 26;
    }

    function getNotConcealedFan() {
        return 26;
    }

    function getRequiredTileSeries() {
        return [
            TileSeries::create(TileSeries::THIRTEEN_ORPHANS)
        ];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isThirteenOrphan(true);
    }

    function getExcludedYakus() {
        return [
            ThirteenOrphansYaku::create()
        ];
    }
}