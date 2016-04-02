<?php
namespace Saki\Win\Yaku\Yakuman2;

use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;
use Saki\Win\Yaku\Yakuman\ThirteenOrphansYaku;

class ThirteenOrphansPairWaitingYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 26;
    }

    protected function getNotConcealedFanCount() {
        return 26;
    }

    protected function getRequiredTileSeries() {
        return [
            TileSeries::getInstance(TileSeries::THIRTEEN_ORPHANS)
        ];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isThirteenOrphan(true);
    }

    function getExcludedYakus() {
        return [
            ThirteenOrphansYaku::getInstance()
        ];
    }
}