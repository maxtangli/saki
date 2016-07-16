<?php
namespace Saki\Win\Yaku\Yakuman2;

use Saki\Win\Series\Series;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;
use Saki\Win\Yaku\Yakuman\ThirteenOrphansYaku;

/**
 * 国士無双十三面待ち
 * @package Saki\Win\Yaku\Yakuman2
 */
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
        $targetTile = $subTarget->getTarget()->getTile();
        return $subTarget->getAllMeldList()->isThirteenOrphan(true, $targetTile);
    }

    function getExcludedYakus() {
        return [ThirteenOrphansYaku::create()];
    }
}