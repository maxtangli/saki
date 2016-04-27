<?php
namespace Saki\Win\Yaku\Yakuman2;

use Saki\Win\Series\Series;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;
use Saki\Win\Yaku\Yakuman\ThirteenOrphansYaku;

class PureThirteenOrphansYaku extends Yaku {
    function getConcealedFan() {
        return 26;
    }

    function getNotConcealedFan() {
        return 26;
    }

    function getRequiredSeries() {
        return [
            Series::create(Series::THIRTEEN_ORPHANS)
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