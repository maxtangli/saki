<?php
namespace Saki\Win\Yaku\Yakuman;

use Saki\Win\Series;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class ThirteenOrphansYaku extends Yaku {
    function getConcealedFan() {
        return 13;
    }

    function getNotConcealedFan() {
        return 13;
    }

    function getRequiredSeries() {
        return [
            Series::create(Series::THIRTEEN_ORPHANS)
        ];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isThirteenOrphan(false);
    }
}

