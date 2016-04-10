<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class KingSTileWinYaku extends Yaku {
    function getConcealedFanCount() {
        return 1;
    }

    function getNotConcealedFanCount() {
        return 1;
    }

    function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->isKingSTileWin();
    }
}

